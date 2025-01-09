<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\AutoUpgrade\Commands;

use Exception;
use InvalidArgumentException;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\Runner\AllRestoreTasks;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreCommand extends AbstractBackupCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'backup:restore';

    protected function configure(): void
    {
        $this
            ->setDescription('Restore the store to a previous state from a backup file.')
            ->setHelp(
                'This command allows you to restore the store to a previous state from a backup file.' .
                'See https://devdocs.prestashop-project.org/8/basics/keeping-up-to-date/upgrade-module/upgrade-cli/#rollback-cli for more details'
            )
            ->addArgument('admin-dir', InputArgument::REQUIRED, 'The admin directory name.')
            ->addOption('backup', null, InputOption::VALUE_REQUIRED, 'Specify the backup name to restore (this can be found in your folder <admin directory>/autoupgrade/backup/)');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $this->setupEnvironment($input, $output);

            $backup = $input->getOption('backup');

            if (!$backup) {
                if (!$input->isInteractive()) {
                    throw new InvalidArgumentException("The '--backup' option is required.");
                }

                $backup = $this->selectBackupInteractive($input, $output);

                if (!$backup) {
                    return ExitCode::SUCCESS;
                }
            }
            $this->upgradeContainer->getFileStorage()->cleanAllRestoreFiles();
            $controller = new AllRestoreTasks($this->upgradeContainer);
            $controller->setOptions([
                'backup' => $backup,
            ]);
            $controller->init();
            $exitCode = $controller->run();
            $this->logger->debug('Process completed with exit code: ' . $exitCode);

            return $exitCode;
        } catch (Exception $e) {
            $this->logger->error("An error occurred during the restoration process:\n" . $e);
            throw $e;
        }
    }
}
