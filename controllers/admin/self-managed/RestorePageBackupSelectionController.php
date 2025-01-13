<?php

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\AjaxResponseBuilder;
use PrestaShop\Module\AutoUpgrade\Exceptions\BackupException;
use PrestaShop\Module\AutoUpgrade\Parameters\RestoreConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\RestoreSteps;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\Stepper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class RestorePageBackupSelectionController extends AbstractPageWithStepController
{
    const CURRENT_STEP = RestoreSteps::STEP_BACKUP_SELECTION;
    const FORM_NAME = 'backup_choice';
    const DELETE_BACKUP_FORM_NAME = 'backup_to_delete';
    const RESTORE_BACKUP_FORM_NAME = 'backup_to_restore';
    const FORM_FIELDS = [
        RestoreConfiguration::BACKUP_NAME => RestoreConfiguration::BACKUP_NAME,
    ];

    public function index()
    {
        $backups = $this->upgradeContainer->getBackupFinder()->getAvailableBackups();

        if (!empty($backups)) {
            return parent::index();
        }

        return AjaxResponseBuilder::nextRouteResponse(Routes::HOME_PAGE);
    }

    protected function getPageTemplate(): string
    {
        return 'restore';
    }

    protected function getStepTemplate(): string
    {
        return self::CURRENT_STEP;
    }

    protected function displayRouteInUrl(): ?string
    {
        return Routes::RESTORE_PAGE_BACKUP_SELECTION;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $restoreSteps = new Stepper($this->upgradeContainer->getTranslator(), TaskType::TASK_TYPE_RESTORE);

        $backupsAvailable = $this->upgradeContainer->getBackupFinder()->getSortedAndFormatedAvailableBackups();

        $currentConfiguration = new RestoreConfiguration($this->upgradeContainer->getFileStorage()->load(UpgradeFileNames::RESTORE_CONFIG_FILENAME));

        $currentBackup = $currentConfiguration->getBackupName() ?? $backupsAvailable[0]['filename'];

        return array_merge(
            $restoreSteps->getStepParams($this::CURRENT_STEP),
            [
                'form_backup_selection_name' => self::FORM_NAME,
                'form_route_to_save' => Routes::RESTORE_STEP_BACKUP_SELECTION_SAVE_FORM,
                'form_route_to_submit_restore' => Routes::RESTORE_STEP_BACKUP_SELECTION_SUBMIT_RESTORE_FORM,
                'form_route_to_submit_delete' => Routes::RESTORE_STEP_BACKUP_SELECTION_SUBMIT_DELETE_FORM,
                'form_fields' => self::FORM_FIELDS,
                'current_backup' => $currentBackup,
                'backups_available' => $backupsAvailable,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     *
     * @throws BackupException
     */
    private function getDialogParams(): array
    {
        $backupName = $this->request->request->get(RestoreConfiguration::BACKUP_NAME);
        $backupDate = $this->upgradeContainer->getBackupFinder()->parseBackupMetadata($backupName)['datetime'];

        return [
            'backup_name' => $backupName,
            'backup_date' => $backupDate,
            'form_fields' => self::FORM_FIELDS,
        ];
    }

    /**
     * @throws BackupException
     */
    public function submitDelete(): JsonResponse
    {
        $onlyBackup = count($this->upgradeContainer->getBackupFinder()->getAvailableBackups()) === 1;

        return $this->displayDialog('dialog-delete-backup',
            array_merge(
                $this->getDialogParams(),
                [
                    'only_backup' => $onlyBackup,
                    'form_name' => self::DELETE_BACKUP_FORM_NAME,
                    'form_route_to_confirm_delete' => Routes::RESTORE_STEP_BACKUP_SELECTION_CONFIRM_DELETE_FORM,
                ]
            ),
            'delete-backup-dialog'
        );
    }

    public function confirmDelete(): JsonResponse
    {
        $backup = $this->request->request->get(self::FORM_FIELDS[RestoreConfiguration::BACKUP_NAME]);
        $this->upgradeContainer->getBackupManager()->deleteBackup($backup);

        return AjaxResponseBuilder::nextRouteResponse(Routes::RESTORE_PAGE_BACKUP_SELECTION);
    }

    /**
     * @throws \Exception
     */
    public function save(): JsonResponse
    {
        $this->saveBackupConfiguration();

        return AjaxResponseBuilder::nextRouteResponse(Routes::RESTORE_STEP_BACKUP_SELECTION);
    }

    /**
     * @throws BackupException
     */
    public function submitRestore(): JsonResponse
    {
        $backupName = $this->request->request->get(RestoreConfiguration::BACKUP_NAME);
        $backupVersion = $this->upgradeContainer->getBackupFinder()->parseBackupMetadata($backupName)['version'];

        return $this->displayDialog('dialog-restore-from-backup',
            array_merge(
                $this->getDialogParams(),
                [
                    'backup_version' => $backupVersion,
                    'form_name' => self::RESTORE_BACKUP_FORM_NAME,
                    'form_route_to_confirm_restore' => Routes::RESTORE_STEP_BACKUP_SELECTION_CONFIRM_RESTORE_FORM,
                ]
            ),
            'restore-backup-dialog'
        );
    }

    /**
     * @throws \Exception
     */
    public function startRestore(): JsonResponse
    {
        $this->saveBackupConfiguration();

        return AjaxResponseBuilder::nextRouteResponse(Routes::RESTORE_STEP_RESTORE);
    }

    /**
     * @throws \Exception
     */
    private function saveBackupConfiguration(): void
    {
        $configurationStorage = $this->upgradeContainer->getConfigurationStorage();
        $restoreConfiguration = $this->upgradeContainer->getRestoreConfiguration();

        $config = [
            RestoreConfiguration::BACKUP_NAME => $this->request->request->get(RestoreConfiguration::BACKUP_NAME),
        ];

        $restoreConfiguration->merge($config);
        $configurationStorage->save($restoreConfiguration);
    }

    /**
     * @param array<string, mixed> $params
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function displayDialog(string $dialogName, array $params, string $scriptName): JsonResponse
    {
        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::DIALOG_PARENT_ID,
            $this->getTwig()->render(
                '@ModuleAutoUpgrade/dialogs/' . $dialogName . '.html.twig',
                $params
            ),
            ['addScript' => $scriptName]
        );
    }
}
