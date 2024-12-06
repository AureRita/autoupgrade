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

namespace PrestaShop\Module\AutoUpgrade;

use Configuration;
use ConfigurationTest;
use Context;
use Exception;
use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Services\PhpVersionResolverService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use PrestaShop\Module\AutoUpgrade\Xml\ChecksumCompare;
use Shop;

class UpgradeSelfCheck
{
    /** @var bool */
    private $fOpenOrCurlEnabled;
    /** @var bool */
    private $zipEnabled;
    /** @var bool */
    private $rootDirectoryWritable;
    /** @var bool */
    private $adminAutoUpgradeDirectoryWritable;
    /** @var string */
    private $adminAutoUpgradeDirectoryWritableReport = '';
    /** @var bool */
    private $shopDeactivated;
    /** @var bool */
    private $localEnvironement;
    /** @var bool */
    private $cacheDisabled;
    /** @var bool */
    private $safeModeDisabled;
    /** @var bool|mixed */
    private $moduleVersionIsLatest;
    /** @var int */
    private $maxExecutionTime;
    /** @var Upgrader */
    private $upgrader;
    /** @var State */
    private $state;
    /**
     * Path to the root folder of PS
     *
     * @var string
     */
    private $prodRootPath;
    /**
     * Path to the admin folder of PS
     *
     * @var string
     */
    private $adminPath;
    /**
     * Path to the root folder of the upgrade module
     *
     * @var string
     */
    private $autoUpgradePath;
    /** @var PrestashopConfiguration */
    private $prestashopConfiguration;
    /** @var UpgradeConfiguration */
    private $upgradeConfiguration;
    /** @var PhpVersionResolverService */
    private $phpRequirementService;
    /** @var Translator */
    private $translator;
    /** @var ChecksumCompare */
    private $checksumCompare;

    // Errors const
    const PHP_COMPATIBILITY_INVALID = 0;
    const ROOT_DIRECTORY_NOT_WRITABLE = 1;
    const ADMIN_UPGRADE_DIRECTORY_NOT_WRITABLE = 2;
    const SAFE_MODE_ENABLED = 3;
    const F_OPEN_AND_CURL_DISABLED = 4;
    const ZIP_DISABLED = 5;
    const MAINTENANCE_MODE_DISABLED = 6;
    const CACHE_ENABLED = 7;
    const MAX_EXECUTION_TIME_VALUE_INCORRECT = 8;
    const APACHE_MOD_REWRITE_DISABLED = 9;
    const NOT_LOADED_PHP_EXTENSIONS_LIST_NOT_EMPTY = 10;
    const NOT_EXIST_PHP_FUNCTIONS_LIST_NOT_EMPTY = 11;
    const MEMORY_LIMIT_INVALID = 12;
    const PHP_FILE_UPLOADS_CONFIGURATION_DISABLED = 13;
    const KEY_GENERATION_INVALID = 14;
    const NOT_WRITING_DIRECTORY_LIST_NOT_EMPTY = 15;
    const SHOP_VERSION_NOT_MATCHING_VERSION_IN_DATABASE = 16;

    // Warnings const
    const MODULE_VERSION_IS_OUT_OF_DATE = 17;
    const PHP_COMPATIBILITY_UNKNOWN = 18;
    const CORE_TEMPERED_FILES_LIST_NOT_EMPTY = 19;
    const THEME_TEMPERED_FILES_LIST_NOT_EMPTY = 20;

    public function __construct(
        Upgrader $upgrader,
        State $state,
        UpgradeConfiguration $upgradeConfiguration,
        PrestashopConfiguration $prestashopConfiguration,
        Translator $translator,
        PhpVersionResolverService $phpRequirementService,
        ChecksumCompare $checksumCompare,
        string $prodRootPath,
        string $adminPath,
        string $autoUpgradePath
    ) {
        $this->upgrader = $upgrader;
        $this->state = $state;
        $this->upgradeConfiguration = $upgradeConfiguration;
        $this->prestashopConfiguration = $prestashopConfiguration;
        $this->translator = $translator;
        $this->phpRequirementService = $phpRequirementService;
        $this->checksumCompare = $checksumCompare;
        $this->prodRootPath = $prodRootPath;
        $this->adminPath = $adminPath;
        $this->autoUpgradePath = $autoUpgradePath;
    }

    /**
     * @throws Exception
     *
     * @return array<int, bool>
     */
    public function getErrors(): array
    {
        $errors = [
            self::ROOT_DIRECTORY_NOT_WRITABLE => !$this->isRootDirectoryWritable(),
            self::ADMIN_UPGRADE_DIRECTORY_NOT_WRITABLE => !$this->isAdminAutoUpgradeDirectoryWritable(),
            self::SAFE_MODE_ENABLED => !$this->isSafeModeDisabled(),
            self::F_OPEN_AND_CURL_DISABLED => !$this->isFOpenOrCurlEnabled(),
            self::ZIP_DISABLED => !$this->isZipEnabled(),
            self::MAINTENANCE_MODE_DISABLED => !$this->isLocalEnvironment() && !$this->isShopDeactivated(),
            self::CACHE_ENABLED => !$this->isCacheDisabled(),
            self::MAX_EXECUTION_TIME_VALUE_INCORRECT => $this->getMaxExecutionTime() > 0 && $this->getMaxExecutionTime() < 30,
            self::APACHE_MOD_REWRITE_DISABLED => !$this->isApacheModRewriteEnabled(),
            self::NOT_LOADED_PHP_EXTENSIONS_LIST_NOT_EMPTY => !empty($this->getNotLoadedPhpExtensions()),
            self::NOT_EXIST_PHP_FUNCTIONS_LIST_NOT_EMPTY => !empty($this->getNotExistsPhpFunctions()),
            self::MEMORY_LIMIT_INVALID => !$this->isMemoryLimitValid(),
            self::PHP_FILE_UPLOADS_CONFIGURATION_DISABLED => !$this->isPhpFileUploadsConfigurationEnabled(),
            self::KEY_GENERATION_INVALID => !$this->checkKeyGeneration(),
            self::NOT_WRITING_DIRECTORY_LIST_NOT_EMPTY => !empty($this->getNotWritingDirectories()),
            self::SHOP_VERSION_NOT_MATCHING_VERSION_IN_DATABASE => !$this->isShopVersionMatchingVersionInDatabase(),
        ];

        if ($this->upgradeConfiguration->isChannelLocal()) {
            $errors[self::PHP_COMPATIBILITY_INVALID] = $this->getPhpRequirementsState() === PhpVersionResolverService::COMPATIBILITY_INVALID;
        }

        return array_filter($errors);
    }

    /**
     * @return array<int, bool>
     *
     * @throws UpgradeException
     */
    public function getWarnings(): array
    {
        $warnings = [
            self::MODULE_VERSION_IS_OUT_OF_DATE => !$this->isModuleVersionLatest(),
            self::CORE_TEMPERED_FILES_LIST_NOT_EMPTY => !empty($this->getCoreAlteredFiles()) || !empty($this->getCoreMissingFiles()),
            self::THEME_TEMPERED_FILES_LIST_NOT_EMPTY => !empty($this->getThemeAlteredFiles()) || !empty($this->getThemeMissingFiles()),
        ];

        if ($this->upgradeConfiguration->isChannelLocal()) {
            $warnings[self::PHP_COMPATIBILITY_UNKNOWN] = $this->getPhpRequirementsState() === PhpVersionResolverService::COMPATIBILITY_UNKNOWN;
        }

        return array_filter($warnings);
    }

    /**
     * @param int $requirement
     *
     * @return array{'message': string, 'list'?: array<string>}
     *
     * @throws UpgradeException
     * @throws DistributionApiException
     */
    public function getRequirementWording(int $requirement, bool $isWebVersion = false): array
    {
        $version = $this->state->getDestinationVersion();
        $phpCompatibilityRange = $this->phpRequirementService->getPhpCompatibilityRange($version);

        switch ($requirement) {
            case self::PHP_COMPATIBILITY_INVALID:
                return [
                    'message' => $this->translator->trans(
                        'Your current PHP version isn\'t compatible with your PrestaShop version. (Expected: %s - %s | Current: %s)',
                        [$phpCompatibilityRange['php_min_version'], $phpCompatibilityRange['php_max_version'], $phpCompatibilityRange['php_current_version']]
                    ),
                ];
            case self::ROOT_DIRECTORY_NOT_WRITABLE:
                return [
                    'message' => $this->translator->trans('Your store\'s root directory isn\'t writable. Provide write access to the user running PHP with appropriate permission & ownership.'),
                ];

            case self::ADMIN_UPGRADE_DIRECTORY_NOT_WRITABLE:
                return [
                    'message' => $this->translator->trans('The "/admin/autoupgrade" directory isn\'t writable. Provide write access to the user running PHP with appropriate permission & ownership.'),
                ];

            case self::SAFE_MODE_ENABLED:
                return [
                    'message' => $this->translator->trans('PHP\'s "Safe mode" needs to be disabled.'),
                ];

            case self::F_OPEN_AND_CURL_DISABLED:
                return [
                    'message' => $this->translator->trans('Files can\'t be downloaded. Enable PHP\'s "allow_url_fopen" option or install PHP extension "cURL".'),
                ];
            case self::ZIP_DISABLED:
                return [
                    'message' => $this->translator->trans('Missing PHP extension "zip".'),
                ];
            case self::MAINTENANCE_MODE_DISABLED:
                if ($isWebVersion) {
                    $maintenanceLink = Context::getContext()->link->getAdminLink('AdminMaintenance');
                    $params = [
                        '[1]' => '<a class="link" href=' . $maintenanceLink . ' target="_blank">',
                        '[/1]' => '</a>',
                    ];
                } else {
                    $params = [
                        '[1]' => '',
                        '[/1]' => '',
                    ];
                }

                return [
                    'message' => $this->translator->trans('Maintenance mode needs to be enabled. Enable maintenance mode and add your maintenance IP in [1]Shop parameters > General > Maintenance[/1].', $params),
                ];
            case self::CACHE_ENABLED:
                return [
                    'message' => $this->translator->trans('PrestaShop\'s caching features needs to be disabled.'),
                ];

            case self::MAX_EXECUTION_TIME_VALUE_INCORRECT:
                return [
                    'message' => $this->translator->trans(
                        'PHP\'s max_execution_time setting needs to have a high value or needs to be disabled entirely (current value: %s seconds)',
                        [$this->getMaxExecutionTime()]
                    ),
                ];

            case self::APACHE_MOD_REWRITE_DISABLED:
                return [
                    'message' => $this->translator->trans('Apache mod_rewrite needs to be enabled.'),
                ];

            case self::NOT_LOADED_PHP_EXTENSIONS_LIST_NOT_EMPTY:
                return [
                    'message' => $this->translator->trans('The following PHP extensions need to be installed: '),
                    'list' => $this->getNotLoadedPhpExtensions(),
                ];

            case self::NOT_EXIST_PHP_FUNCTIONS_LIST_NOT_EMPTY:
                return [
                    'message' => $this->translator->trans('The following PHP functions need to be allowed: '),
                    'list' => $this->getNotExistsPhpFunctions(),
                ];

            case self::MEMORY_LIMIT_INVALID:
                return [
                    'message' => $this->translator->trans('PHP memory_limit needs to be greater than 256 MB.'),
                ];

            case self::PHP_FILE_UPLOADS_CONFIGURATION_DISABLED:
                return [
                    'message' => $this->translator->trans('PHP file_uploads configuration needs to be enabled.'),
                ];
            case self::KEY_GENERATION_INVALID:
                return [
                    'message' => $this->translator->trans('Unable to generate private keys using openssl_pkey_new. Check your OpenSSL configuration, especially the path to openssl.cafile.'),
                ];

            case self::NOT_WRITING_DIRECTORY_LIST_NOT_EMPTY:
                return [
                    'message' => $this->translator->trans('It\'s not possible to write in the following folders, please provide write access to the user running PHP with appropriate permission & ownership: '),
                    'list' => $this->getNotWritingDirectories(),
                ];

            case self::SHOP_VERSION_NOT_MATCHING_VERSION_IN_DATABASE:
                return [
                    'message' => $this->translator->trans('The version of PrestaShop does not match the one stored in database. Your database structure may not be up-to-date and/or the value of PS_VERSION_DB needs to be updated in the configuration table.'),
                ];

            case self::MODULE_VERSION_IS_OUT_OF_DATE:
                return [
                    'message' => $this->translator->trans('Your current version of the module is outdated.'),
                ];

            case self::PHP_COMPATIBILITY_UNKNOWN:
                return [
                    'message' => $this->translator->trans('We were unable to check your PHP compatibility with the destination PrestaShop version.'),
                ];

            case self::CORE_TEMPERED_FILES_LIST_NOT_EMPTY:
                if ($isWebVersion) {
                    // TODO the link must be integrated when implementing the modal in the web part
                    $params = [
                        '[1]' => '<a class="" href="" target="">',
                        '[/1]' => '</a>',
                    ];
                    $message = $this->translator->trans('Some core files have been altered or removed. Any changes made to these files may be overwritten during the update. [1]See the list of alterations.[/1]', $params);
                } else {
                    $message = $this->translator->trans('Some core files have been altered or removed. Any changes made to these files may be overwritten during the update.');
                }

                return [
                    'message' => $message,
                ];

            case self::THEME_TEMPERED_FILES_LIST_NOT_EMPTY:
                if ($isWebVersion) {
                    // TODO the link must be integrated when implementing the modal in the web part
                    $params = [
                        '[1]' => '<a class="" href="" target="">',
                        '[/1]' => '</a>',
                    ];
                    $message = $this->translator->trans('Some theme files have been altered or removed. Any changes made to these files may be overwritten during the update. [1]See the list of alterations.[/1]', $params);
                } else {
                    $message = $this->translator->trans('Some theme files have been altered or removed. Any changes made to these files may be overwritten during the update.');
                }

                return [
                    'message' => $message,
                ];

            default:
                return [
                    'message' => $this->translator->trans('Unknown requirement.'),
                ];
        }
    }

    /**
     * @return string[]
     */
    public function getCoreMissingFiles(): array
    {
        $missingFiles = $this->checksumCompare->getTamperedFilesOnShop($this->state->getCurrentVersion());

        return array_merge($missingFiles['core']['missing'], $missingFiles['mail']['missing']);
    }

    /**
     * @return string[]
     */
    public function getCoreAlteredFiles(): array
    {
        $alteredFiles = $this->checksumCompare->getTamperedFilesOnShop($this->state->getCurrentVersion());

        return array_merge($alteredFiles['core']['altered'], $alteredFiles['mail']['altered']);
    }

    /**
     * @return string[]
     */
    public function getThemeMissingFiles(): array
    {
        $missingFiles = $this->checksumCompare->getTamperedFilesOnShop($this->state->getCurrentVersion());

        return $missingFiles['themes']['missing'];
    }

    /**
     * @return string[]
     */
    public function getThemeAlteredFiles(): array
    {
        $alteredFiles = $this->checksumCompare->getTamperedFilesOnShop($this->state->getCurrentVersion());

        return $alteredFiles['themes']['altered'];
    }

    public function isFOpenOrCurlEnabled(): bool
    {
        if (null !== $this->fOpenOrCurlEnabled) {
            return $this->fOpenOrCurlEnabled;
        }

        return $this->fOpenOrCurlEnabled = ConfigurationTest::test_fopen() || extension_loaded('curl');
    }

    public function isZipEnabled(): bool
    {
        if (null !== $this->zipEnabled) {
            return $this->zipEnabled;
        }

        return $this->zipEnabled = extension_loaded('zip');
    }

    public function isRootDirectoryWritable(): bool
    {
        if (null !== $this->rootDirectoryWritable) {
            return $this->rootDirectoryWritable;
        }

        return $this->rootDirectoryWritable = $this->checkRootWritable();
    }

    public function isAdminAutoUpgradeDirectoryWritable(): bool
    {
        if (null !== $this->adminAutoUpgradeDirectoryWritable) {
            return $this->adminAutoUpgradeDirectoryWritable;
        }

        return $this->adminAutoUpgradeDirectoryWritable = $this->checkAdminDirectoryWritable($this->prodRootPath, $this->adminPath, $this->autoUpgradePath);
    }

    public function getAdminAutoUpgradeDirectoryWritableReport(): string
    {
        return $this->adminAutoUpgradeDirectoryWritableReport;
    }

    public function isShopDeactivated(): bool
    {
        if (null !== $this->shopDeactivated) {
            return $this->shopDeactivated;
        }

        return $this->shopDeactivated = $this->checkShopIsDeactivated();
    }

    public function isLocalEnvironment(): bool
    {
        if (null !== $this->localEnvironement) {
            return $this->localEnvironement;
        }

        return $this->localEnvironement = $this->checkIsLocalEnvironment();
    }

    public function isCacheDisabled(): bool
    {
        if (null !== $this->cacheDisabled) {
            return $this->cacheDisabled;
        }

        return $this->cacheDisabled = !(defined('_PS_CACHE_ENABLED_') && false != _PS_CACHE_ENABLED_);
    }

    public function isSafeModeDisabled(): bool
    {
        if (null !== $this->safeModeDisabled) {
            return $this->safeModeDisabled;
        }

        return $this->safeModeDisabled = $this->checkSafeModeIsDisabled();
    }

    /**
     * @throws UpgradeException
     */
    public function isModuleVersionLatest(): bool
    {
        if (null !== $this->moduleVersionIsLatest) {
            return $this->moduleVersionIsLatest;
        }

        return $this->moduleVersionIsLatest = $this->checkModuleVersionIsLastest();
    }

    public function getModuleVersion(): ?string
    {
        return $this->prestashopConfiguration->getModuleVersion();
    }

    public function getMaxExecutionTime(): int
    {
        if (null !== $this->maxExecutionTime) {
            return $this->maxExecutionTime;
        }

        return $this->maxExecutionTime = $this->checkMaxExecutionTime();
    }

    /**
     * @throws Exception
     */
    public function isShopVersionMatchingVersionInDatabase(): bool
    {
        return version_compare(
            Configuration::get('PS_VERSION_DB'),
            $this->prestashopConfiguration->getPrestaShopVersion(),
            '=='
        );
    }

    /**
     * Indicates if the self check status allows going ahead with the upgrade.
     *
     * @throws Exception
     */
    public function isOkForUpgrade(): bool
    {
        return empty($this->getErrors());
    }

    public function isApacheModRewriteEnabled(): bool
    {
        if (class_exists(ConfigurationTest::class) && is_callable([ConfigurationTest::class, 'test_apache_mod_rewrite'])) {
            return ConfigurationTest::test_apache_mod_rewrite();
        }

        return true;
    }

    public function checkKeyGeneration(): bool
    {
        // Check if key is needed on the version we are upgrading to, if lower, not needed
        if (version_compare($this->state->getDestinationVersion(), '8.1.0', '<')) {
            return true;
        }

        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($privateKey === false) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string>
     */
    public function getNotLoadedPhpExtensions(): array
    {
        if (!class_exists(ConfigurationTest::class)) {
            return [];
        }
        $extensions = [];
        foreach ([
                     'curl', 'dom', 'fileinfo', 'gd', 'intl', 'json', 'mbstring', 'openssl', 'pdo_mysql', 'simplexml', 'zip',
                 ] as $extension) {
            $method = 'test_' . $extension;
            if (method_exists(ConfigurationTest::class, $method) && !ConfigurationTest::$method()) {
                $extensions[] = $extension;
            }
        }

        return $extensions;
    }

    /**
     * @return array<string>
     */
    public function getNotExistsPhpFunctions(): array
    {
        if (!class_exists(ConfigurationTest::class)) {
            return [];
        }
        $functions = [];
        foreach ([
                     'fopen', 'fclose', 'fread', 'fwrite', 'rename', 'file_exists', 'unlink', 'rmdir', 'mkdir', 'getcwd',
                     'chdir', 'chmod',
                 ] as $function) {
            if (!ConfigurationTest::test_system([$function])) {
                $functions[] = $function;
            }
        }

        return $functions;
    }

    /**
     * @return bool
     */
    public function isMemoryLimitValid(): bool
    {
        if (class_exists(ConfigurationTest::class) && is_callable([ConfigurationTest::class, 'test_memory_limit'])) {
            return ConfigurationTest::test_memory_limit();
        }

        return true;
    }

    public function isPhpFileUploadsConfigurationEnabled(): bool
    {
        if (!class_exists(ConfigurationTest::class)) {
            return true;
        }

        return (bool) ConfigurationTest::test_upload();
    }

    public function getPhpRequirementsState(): int
    {
        $version = $this->state->getDestinationVersion();

        return $this->phpRequirementService->getPhpRequirementsState(PHP_VERSION_ID, $version);
    }

    /**
     * @return array<string>
     */
    public function getNotWritingDirectories(): array
    {
        if (!class_exists(ConfigurationTest::class)) {
            return [];
        }

        $tests = ConfigurationTest::getDefaultTests();

        $directories = [];
        foreach ([
                     'cache_dir', 'log_dir', 'img_dir', 'module_dir', 'theme_lang_dir', 'theme_pdf_lang_dir', 'theme_cache_dir',
                     'translations_dir', 'customizable_products_dir', 'virtual_products_dir', 'config_sf2_dir', 'config_dir',
                     'mails_dir', 'translations_sf2',
                 ] as $testKey) {
            if (isset($tests[$testKey]) && !ConfigurationTest::{'test_' . $testKey}($tests[$testKey])) {
                $directories[] = $tests[$testKey];
            }
        }

        return $directories;
    }

    /**
     * Get the server variable REMOTE_ADDR, or the first ip of HTTP_X_FORWARDED_FOR (when using proxy).
     *
     * @return string $remote_addr ip of client
     */
    private function getRemoteAddr(): string
    {
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            $headers = $_SERVER;
        }

        if (array_key_exists('X-Forwarded-For', $headers)) {
            $_SERVER['HTTP_X_FORWARDED_FOR'] = $headers['X-Forwarded-For'];
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && (!isset($_SERVER['REMOTE_ADDR'])
                || preg_match('/^127\..*/i', trim($_SERVER['REMOTE_ADDR'])) || preg_match('/^172\.(1[6-9]|2\d|30|31)\..*/i', trim($_SERVER['REMOTE_ADDR']))
                || preg_match('/^192\.168\.*/i', trim($_SERVER['REMOTE_ADDR'])) || preg_match('/^10\..*/i', trim($_SERVER['REMOTE_ADDR'])))) {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
                $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                return $ips[0];
            } else {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    private function checkRootWritable(): bool
    {
        // Root directory permissions cannot be checked recursively anymore, it takes too much time
        return ConfigurationTest::test_dir('/', false);
    }

    /**
     * @throws UpgradeException
     */
    private function checkModuleVersionIsLastest(): bool
    {
        return version_compare($this->getModuleVersion(), $this->upgrader->getLatestModuleVersion(), '>=');
    }

    private function checkIsLocalEnvironment(): bool
    {
        return in_array($this->getRemoteAddr(), ['127.0.0.1', 'localhost', '[::1]', '::1']);
    }

    private function checkShopIsDeactivated(): bool
    {
        // if multistore is not active, just check if shop is enabled and has a maintenance IP
        if (!Shop::isFeatureActive()) {
            return !Configuration::get('PS_SHOP_ENABLE') && Configuration::get('PS_MAINTENANCE_IP');
        }

        // multistore is active: all shops must be deactivated and have a maintenance IP, otherwise return false
        foreach (Shop::getCompleteListOfShopsID() as $shopId) {
            $shop = new Shop((int) $shopId);
            $groupId = (int) $shop->getGroup()->id;
            $isEnabled = Configuration::get('PS_SHOP_ENABLE', null, $groupId, (int) $shopId);
            $maintenanceIp = Configuration::get('PS_MAINTENANCE_IP', null, $groupId, (int) $shopId);

            if ($isEnabled || !$maintenanceIp) {
                return false;
            }
        }

        return true;
    }

    private function checkAdminDirectoryWritable(string $prodRootPath, string $adminPath, string $adminAutoUpgradePath): bool
    {
        $relativeDirectory = trim(str_replace($prodRootPath, '', $adminAutoUpgradePath), DIRECTORY_SEPARATOR);

        return ConfigurationTest::test_dir(
            $relativeDirectory,
            false,
            $this->adminAutoUpgradeDirectoryWritableReport
        );
    }

    private function checkSafeModeIsDisabled(): bool
    {
        $safeMode = @ini_get('safe_mode');
        if (empty($safeMode)) {
            $safeMode = '';
        }

        return !in_array(strtolower($safeMode), [1, 'on']);
    }

    private function checkMaxExecutionTime(): int
    {
        return (int) @ini_get('max_execution_time');
    }
}
