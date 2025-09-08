<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
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
    /** @var string */
    private $latestCompatibleModuleVersion;
    /** @var bool|mixed */
    private $moduleVersionIsLatest;
    /** @var int */
    private $maxExecutionTime;
    /** @var Upgrader */
    private $upgrader;
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
    /** @var string */
    private $currentVersion;
    /** @var string */
    private $destinationVersion;
    /** @var PrestashopConfiguration */
    private $prestashopConfiguration;
    /** @var UpgradeConfiguration */
    private $updateConfiguration;
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
    const TEMPERED_FILES_UNKNOWN = 17;

    // Warnings const
    const MODULE_VERSION_IS_OUT_OF_DATE = 18;
    const PHP_COMPATIBILITY_UNKNOWN = 19;
    const CORE_TEMPERED_FILES_LIST_NOT_EMPTY = 20;
    const THEME_TEMPERED_FILES_LIST_NOT_EMPTY = 21;
    const DESTINATION_VERSION_IS_NOT_SUPPORTED = 22;

    public function __construct(
        Upgrader $upgrader,
        UpgradeConfiguration $updateConfiguration,
        PrestashopConfiguration $prestashopConfiguration,
        Translator $translator,
        PhpVersionResolverService $phpRequirementService,
        ChecksumCompare $checksumCompare,
        string $prodRootPath,
        string $adminPath,
        string $autoUpgradePath,
        string $currentVersion
    ) {
        $this->upgrader = $upgrader;
        $this->updateConfiguration = $updateConfiguration;
        $this->prestashopConfiguration = $prestashopConfiguration;
        $this->translator = $translator;
        $this->phpRequirementService = $phpRequirementService;
        $this->checksumCompare = $checksumCompare;
        $this->prodRootPath = $prodRootPath;
        $this->adminPath = $adminPath;
        $this->autoUpgradePath = $autoUpgradePath;
        $this->currentVersion = $currentVersion;
        $this->destinationVersion = $upgrader->getDestinationVersion();
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
            self::F_OPEN_AND_CURL_DISABLED => !$this->isFOpenOrCurlEnabled(),
            self::ZIP_DISABLED => !$this->isZipEnabled(),
            self::MAINTENANCE_MODE_DISABLED => !$this->isLocalEnvironment() && !$this->isShopDeactivated(),
            self::CACHE_ENABLED => !$this->isCacheDisabled(),
            self::MAX_EXECUTION_TIME_VALUE_INCORRECT => !$this->checkMaxExecutionTimeIsValid(),
            self::APACHE_MOD_REWRITE_DISABLED => !$this->isApacheModRewriteEnabled(),
            self::NOT_LOADED_PHP_EXTENSIONS_LIST_NOT_EMPTY => !empty($this->getNotLoadedPhpExtensions()),
            self::NOT_EXIST_PHP_FUNCTIONS_LIST_NOT_EMPTY => !empty($this->getNotExistsPhpFunctions()),
            self::MEMORY_LIMIT_INVALID => !$this->isMemoryLimitValid(),
            self::PHP_FILE_UPLOADS_CONFIGURATION_DISABLED => !$this->isPhpFileUploadsConfigurationEnabled(),
            self::KEY_GENERATION_INVALID => !$this->checkKeyGeneration(),
            self::NOT_WRITING_DIRECTORY_LIST_NOT_EMPTY => !empty($this->getNotWritingDirectories()),
            self::SHOP_VERSION_NOT_MATCHING_VERSION_IN_DATABASE => !$this->isShopVersionMatchingVersionInDatabase(),
            self::TEMPERED_FILES_UNKNOWN => $this->isAlteredFilesNull(),
        ];

        if ($this->updateConfiguration->isChannelLocal()) {
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
        $warnings = [];

        if ($this->isAlteredFilesNull()) {
            $isCoreTemperedFileListNotEmpty = false;
            $isThemeTemperedFileListNotEmpty = false;
        } else {
            $isCoreTemperedFileListNotEmpty = !empty($this->getCoreAlteredFiles()) || !empty($this->getCoreMissingFiles());
            $isThemeTemperedFileListNotEmpty = !empty($this->getThemeAlteredFiles()) || !empty($this->getThemeMissingFiles());
        }

        $warnings = [
            self::DESTINATION_VERSION_IS_NOT_SUPPORTED => empty($this->getLatestCompatibleModuleVersion()),
            self::THEME_TEMPERED_FILES_LIST_NOT_EMPTY => $isThemeTemperedFileListNotEmpty,
            self::CORE_TEMPERED_FILES_LIST_NOT_EMPTY => $isCoreTemperedFileListNotEmpty,
        ];

        if (!$warnings[self::DESTINATION_VERSION_IS_NOT_SUPPORTED]) {
            $warnings[self::MODULE_VERSION_IS_OUT_OF_DATE] = !$this->isModuleVersionLatest();
        }

        if ($this->updateConfiguration->isChannelLocal()) {
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
        switch ($requirement) {
            case self::PHP_COMPATIBILITY_INVALID:
                $phpCompatibilityRange = $this->phpRequirementService->getPhpCompatibilityRange($this->destinationVersion);

                return [
                    'message' => $this->translator->trans(
                        'Your current PHP version isn\'t compatible with PrestaShop %s. (Expected: %s - %s | Current: %s)',
                        [$this->destinationVersion, $phpCompatibilityRange['php_min_version'], $phpCompatibilityRange['php_max_version'], $phpCompatibilityRange['php_current_version']]
                    ),
                ];

            case self::ROOT_DIRECTORY_NOT_WRITABLE:
                return [
                    'message' => $this->translator->trans(
                        'Your store\'s root directory %s isn\'t writable. Provide write access to the user running PHP with appropriate permission & ownership.',
                        [$this->prodRootPath]
                    ),
                ];

            case self::ADMIN_UPGRADE_DIRECTORY_NOT_WRITABLE:
                return [
                    // TODO: sanitize autoUpgradePath or add generic placeholder for path
                    'message' => $this->translator->trans(
                        'The %s directory isn\'t writable. Provide write access to the user running PHP with appropriate permission & ownership.',
                        [$this->autoUpgradePath]
                    ),
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
                    $psVersion = $this->prestashopConfiguration->getPrestaShopVersion();
                    /* We are using a hardcoded route for version 1.7.4 because handling getAdminLink for the `AdminMaintenance` controller results in an error. */
                    if (version_compare($psVersion, '1.7.4', '>=') && version_compare($psVersion, '1.7.5', '<')) {
                        /** We are not adding the token because there is no way to retrieve it due to the route retrieval issue in 1.7.4. We have taken into consideration that the user will land on an alert page. */
                        $maintenanceLink = Context::getContext()->link->getBaseLink() . basename(_PS_ADMIN_DIR_) . '/configure/shop/maintenance';
                    } else {
                        $maintenanceLink = Context::getContext()->link->getAdminLink('AdminMaintenance');
                    }

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
                if ($isWebVersion) {
                    $cacheLink = Context::getContext()->link->getAdminLink('AdminPerformance');
                    $params = [
                        '[1]' => '<a class="link" href=' . $cacheLink . ' target="_blank">',
                        '[/1]' => '</a>',
                    ];
                } else {
                    $params = [
                        '[1]' => '',
                        '[/1]' => '',
                    ];
                }

                return [
                    'message' => $this->translator->trans('PrestaShop\'s caching features needs to be disabled. Disable caching features in [1]Advanced parameters > Performance > Caching[/1].', $params),
                ];

            case self::MAX_EXECUTION_TIME_VALUE_INCORRECT:
                return [
                    'message' => $this->translator->trans(
                        'PHP\'s max_execution_time setting needs to have a high value or needs to be disabled entirely (current value: %s seconds).',
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
                    'message' => $this->translator->trans('Failed to generate private keys using openssl_pkey_new(). Please check your PHP configuration, especially the openssl.cafile setting, and ensure that the specified file exists and is accessible.'),
                ];

            case self::NOT_WRITING_DIRECTORY_LIST_NOT_EMPTY:
                return [
                    'message' => $this->translator->trans('It\'s not possible to write in the following folders. Provide write access to the user running PHP with appropriate permission & ownership: '),
                    'list' => $this->getNotWritingDirectories(),
                ];

            case self::SHOP_VERSION_NOT_MATCHING_VERSION_IN_DATABASE:
                return [
                    'message' => $this->translator->trans('The version of PrestaShop does not match the one stored in database. Your database structure may not be up-to-date and/or the value of PS_VERSION_DB needs to be updated in the configuration table.'),
                ];

            case self::DESTINATION_VERSION_IS_NOT_SUPPORTED:
                return [
                    'message' => $this->translator->trans('Updates to this PrestaShop version are not officially supported by the Update Assistant module.'),
                ];

            case self::MODULE_VERSION_IS_OUT_OF_DATE:
                if ($isWebVersion) {
                    $moduleUpdateLink = Context::getContext()->link->getAdminLink('AdminModulesUpdates');
                    $params = [
                        '[1]' => '<a class="link" href=' . $moduleUpdateLink . ' target="_blank">',
                        '[/1]' => '</a>',
                    ];
                } else {
                    $params = [
                        '[1]' => '',
                        '[/1]' => '',
                    ];
                }

                return [
                    'message' => $this->translator->trans('Your current version of the module is out of date. Update now [1]Modules > Module Manager > Updates[/1]', $params),
                ];

            case self::PHP_COMPATIBILITY_UNKNOWN:
                return [
                    'message' => $this->translator->trans(
                        'We were unable to check your PHP compatibility with PrestaShop %s.',
                        [$this->destinationVersion]
                    ),
                ];

            case self::CORE_TEMPERED_FILES_LIST_NOT_EMPTY:
                if ($isWebVersion) {
                    $params = [
                        '[1]' => '<a class="link" href="#update-step-version-choice-core-tempered-files-dialog">',
                        '[/1]' => '</a>',
                    ];
                    $message = $this->translator->trans('Some core files have been altered or removed. Any changes made to these files may be overwritten during the update. [1]See the list of changes[/1].', $params);
                } else {
                    $message = $this->translator->trans('Some core files have been altered or removed. Any changes made to these files may be overwritten during the update.');
                }

                return [
                    'message' => $message,
                ];

            case self::THEME_TEMPERED_FILES_LIST_NOT_EMPTY:
                if ($isWebVersion) {
                    $params = [
                        '[1]' => '<a class="link" href="#update-step-version-choice-theme-tempered-files-dialog">',
                        '[/1]' => '</a>',
                    ];
                    $message = $this->translator->trans('Some theme files have been altered or removed. Any changes made to these files may be overwritten during the update. [1]See the list of changes[/1].', $params);
                } else {
                    $message = $this->translator->trans('Some theme files have been altered or removed. Any changes made to these files may be overwritten during the update.');
                }

                return [
                    'message' => $message,
                ];

            case self::TEMPERED_FILES_UNKNOWN:
                return [
                    'message' => $this->translator->trans('We were unable to fetch missing or altered files. In these conditions, your store cannot be updated to avoid any further bugs.'),
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
    public function getCoreMissingFiles(): ?array
    {
        $missingFiles = $this->checksumCompare->getTamperedFilesOnShop($this->currentVersion);
        if (!$missingFiles) {
            return null;
        }

        return array_merge($missingFiles['core']['missing'], $missingFiles['mail']['missing']);
    }

    /**
     * @return string[]
     */
    public function getCoreAlteredFiles(): ?array
    {
        $alteredFiles = $this->checksumCompare->getTamperedFilesOnShop($this->currentVersion);
        if (!$alteredFiles) {
            return null;
        }

        return array_merge($alteredFiles['core']['altered'], $alteredFiles['mail']['altered']);
    }

    /**
     * @return string[]
     */
    public function getThemeMissingFiles(): ?array
    {
        $missingFiles = $this->checksumCompare->getTamperedFilesOnShop($this->currentVersion);
        if (!$missingFiles) {
            return null;
        }

        return $missingFiles['themes']['missing'];
    }

    /**
     * @return string[]
     */
    public function getThemeAlteredFiles(): ?array
    {
        $alteredFiles = $this->checksumCompare->getTamperedFilesOnShop($this->currentVersion);
        if (!$alteredFiles) {
            return null;
        }

        return $alteredFiles['themes']['altered'];
    }

    public function isAlteredFilesNull(): bool
    {
        return $this->getCoreAlteredFiles() === null
            || $this->getCoreMissingFiles() === null
            || $this->getThemeAlteredFiles() === null
            || $this->getThemeMissingFiles() === null;
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

    public function getLatestCompatibleModuleVersion(): string
    {
        if (null !== $this->latestCompatibleModuleVersion) {
            return $this->latestCompatibleModuleVersion;
        }

        return $this->latestCompatibleModuleVersion = $this->upgrader->getLatestCompatibleModuleVersion();
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

        return $this->maxExecutionTime = (int) @ini_get('max_execution_time');
    }

    private function checkMaxExecutionTimeIsValid(): bool
    {
        return $this->getMaxExecutionTime() === 0 || $this->getMaxExecutionTime() >= 30;
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
        if (version_compare($this->destinationVersion, '8.1.0', '<')) {
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

        $neededFunctions = [
            'fopen', 'fclose', 'fread', 'fwrite', 'rename', 'file_exists', 'unlink', 'rmdir', 'mkdir', 'getcwd',
            'chdir', 'chmod',
        ];

        if ($this->destinationVersion === '9.0.0') {
            $neededFunctions[] = 'symlink';
        }

        $missingFunctions = [];
        foreach ($neededFunctions as $function) {
            if (!ConfigurationTest::test_system([$function])) {
                $missingFunctions[] = $function;
            }
        }

        return $missingFunctions;
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
        $version = $this->destinationVersion;

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
            if (isset($tests[$testKey]) && method_exists(ConfigurationTest::class, 'test_' . $testKey) && !ConfigurationTest::{'test_' . $testKey}($tests[$testKey])) {
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
        return version_compare($this->getModuleVersion(), $this->getLatestCompatibleModuleVersion(), '>=');
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
}
