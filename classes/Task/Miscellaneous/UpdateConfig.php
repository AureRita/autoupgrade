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

namespace PrestaShop\Module\AutoUpgrade\Task\Miscellaneous;

use Exception;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

/**
 * update configuration after validating the new values.
 */
class UpdateConfig extends AbstractTask
{
    /**
     * Data being passed by CLI entry point
     *
     * @var array<string, mixed>
     */
    protected $cliParameters;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        // nothing next
        $this->next = TaskName::TASK_COMPLETE;

        // Was coming from AdminSelfUpgrade::currentParams before
        $configurationData = $this->getConfigurationData();
        $config = [];

        foreach (UpgradeConfiguration::UPGRADE_CONST_KEYS as $key) {
            if (!isset($configurationData[$key])) {
                continue;
            }
            // The PS_DISABLE_OVERRIDES variable must only be updated on the database side
            if ($key === UpgradeConfiguration::PS_DISABLE_OVERRIDES) {
                UpgradeConfiguration::updatePSDisableOverrides((bool) $configurationData[$key]);
            } else {
                $config[$key] = $configurationData[$key];
            }
        }

        $isLocal = $config[UpgradeConfiguration::CHANNEL] === UpgradeConfiguration::CHANNEL_LOCAL;

        $error = $this->container->getConfigurationValidator()->validate($config);

        if ($isLocal && empty($error)) {
            $this->container->getLocalChannelConfigurationValidator()->validate($config);
        }

        if (!empty($error)) {
            $this->setErrorFlag();
            $this->logger->error(reset($error)['message']);

            return ExitCode::FAIL;
        }

        if ($isLocal) {
            $file = $config[UpgradeConfiguration::ARCHIVE_ZIP];
            $fullFilePath = $this->container->getProperty(UpgradeContainer::DOWNLOAD_PATH) . DIRECTORY_SEPARATOR . $file;
            try {
                $config['archive_version_num'] = $this->container->getPrestashopVersionService()->extractPrestashopVersionFromZip($fullFilePath);
                $this->logger->info($this->translator->trans('Upgrade process will use archive.'));
            } catch (Exception $exception) {
                $this->setErrorFlag();
                $this->logger->error($this->translator->trans('We couldn\'t find a PrestaShop version in the .zip file that was uploaded in your local archive. Please try again.'));

                return ExitCode::FAIL;
            }
        }

        if (!$this->writeConfig($config)) {
            $this->setErrorFlag();
            $this->logger->error($this->translator->trans('Error on saving configuration'));

            return ExitCode::FAIL;
        }

        return ExitCode::SUCCESS;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function inputCliParameters($parameters): void
    {
        $this->cliParameters = $parameters;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getConfigurationData(): array
    {
        if (null !== $this->cliParameters) {
            return $this->getCLIParams();
        }

        return $this->getRequestParams();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getCLIParams(): array
    {
        if (empty($this->cliParameters)) {
            throw new \RuntimeException('Empty CLI parameters - did CLI entry point failed to provide data?');
        }

        return $this->cliParameters;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getRequestParams(): array
    {
        return empty($_REQUEST['params']) ? $_REQUEST : $_REQUEST['params'];
    }

    /**
     * update module configuration (saved in file UpgradeFiles::configFilename) with $new_config.
     *
     * @param array<string, mixed> $config
     *
     * @return bool true if success
     *
     * @throws Exception
     */
    private function writeConfig(array $config): bool
    {
        $classConfig = $this->container->getUpgradeConfiguration();
        $classConfig->merge($config);

        $this->logger->info($this->translator->trans('Configuration successfully updated.'));

        $this->container->getLogger()->debug('Configuration update: ' . json_encode($classConfig->toArray(), JSON_PRETTY_PRINT));

        return (new UpgradeConfigurationStorage($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR))->save($classConfig, UpgradeFileNames::CONFIG_FILENAME);
    }

    public function init(): void
    {
        $this->container->initPrestaShopCore();
    }
}
