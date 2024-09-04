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
use Exception;
use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use Shop;

class UpgradeSelfCheck
{
    /**
     * @var bool
     */
    private $fOpenOrCurlEnabled;

    /**
     * @var bool
     */
    private $zipEnabled;

    /**
     * @var bool
     */
    private $rootDirectoryWritable;

    /**
     * @var bool
     */
    private $adminAutoUpgradeDirectoryWritable;

    /**
     * @var string
     */
    private $adminAutoUpgradeDirectoryWritableReport = '';

    /**
     * @var bool
     */
    private $shopDeactivated;

    /**
     * @var bool
     */
    private $localEnvironement;

    /**
     * @var bool
     */
    private $cacheDisabled;

    /**
     * @var bool
     */
    private $safeModeDisabled;

    /**
     * @var bool|mixed
     */
    private $moduleVersionIsLatest;

    /**
     * @var string
     */
    private $rootWritableReport;

    /**
     * @var int
     */
    private $maxExecutionTime;

    /**
     * @var array<string, string>
     */
    private $phpCompatibilityRange;

    /**
     * @var string
     */
    private $configDir = '/modules/autoupgrade/config.xml';

    /**
     * @var Upgrader
     */
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

    /**
     * @var PrestashopConfiguration
     */
    private $prestashopConfiguration;

    /**
     * @var DistributionApiService
     */
    private $distributionApiService;

    const PRESTASHOP_17_PHP_REQUIREMENTS = [
        '1.7.0' => [
            'php_min_version' => '5.4',
            'php_max_version' => '7.1',
        ],
        '1.7.1' => [
            'php_min_version' => '5.4',
            'php_max_version' => '7.1',
        ],
        '1.7.2' => [
            'php_min_version' => '5.4',
            'php_max_version' => '7.1',
        ],
        '1.7.3' => [
            'php_min_version' => '5.4',
            'php_max_version' => '7.1',
        ],
        '1.7.4' => [
            'php_min_version' => '5.6',
            'php_max_version' => '7.1',
        ],
        '1.7.5' => [
            'php_min_version' => '5.6',
            'php_max_version' => '7.2',
        ],
        '1.7.6' => [
            'php_min_version' => '5.6',
            'php_max_version' => '7.2',
        ],
        '1.7.7' => [
            'php_min_version' => '5.6',
            'php_max_version' => '7.3',
        ],
        '1.7.8' => [
            'php_min_version' => '5.6',
            'php_max_version' => '7.4',
        ],
    ];

    const PHP_REQUIREMENTS_INVALID = 0;
    const PHP_REQUIREMENTS_VALID = 1;
    const PHP_REQUIREMENTS_UNKNOWN = 2;

    public function __construct(
        Upgrader $upgrader,
        PrestashopConfiguration $prestashopConfiguration,
        DistributionApiService $distributionApiService,
        string $prodRootPath,
        string $adminPath,
        string $autoUpgradePath
    ) {
        $this->upgrader = $upgrader;
        $this->prestashopConfiguration = $prestashopConfiguration;
        $this->distributionApiService = $distributionApiService;
        $this->prodRootPath = $prodRootPath;
        $this->adminPath = $adminPath;
        $this->autoUpgradePath = $autoUpgradePath;
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

    public function isModuleVersionLatest(): bool
    {
        if (null !== $this->moduleVersionIsLatest) {
            return $this->moduleVersionIsLatest;
        }

        return $this->moduleVersionIsLatest = $this->checkModuleVersionIsLastest($this->upgrader);
    }

    public function getRootWritableReport(): string
    {
        if (null !== $this->rootWritableReport) {
            return $this->rootWritableReport;
        }

        $this->rootWritableReport = '';
        $this->isRootDirectoryWritable();

        return $this->rootWritableReport;
    }

    public function getModuleVersion(): ?string
    {
        return $this->prestashopConfiguration->getModuleVersion();
    }

    public function getConfigDir(): string
    {
        return $this->configDir;
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
     */
    public function isOkForUpgrade(): bool
    {
        return
            $this->isFOpenOrCurlEnabled()
            && $this->isZipEnabled()
            && $this->isRootDirectoryWritable()
            && $this->isAdminAutoUpgradeDirectoryWritable()
            && ($this->isShopDeactivated() || $this->isLocalEnvironment())
            && $this->isCacheDisabled()
            && $this->isModuleVersionLatest()
            && $this->getPhpRequirementsState(PHP_VERSION_ID) !== $this::PHP_REQUIREMENTS_INVALID
            && $this->isShopVersionMatchingVersionInDatabase()
            && $this->isApacheModRewriteEnabled()
            && $this->checkKeyGeneration()
            && $this->getNotLoadedPhpExtensions() === []
            && $this->isMemoryLimitValid()
            && $this->isPhpFileUploadsConfigurationEnabled()
            && $this->getNotExistsPhpFunctions() === []
            && $this->isPhpSessionsValid()
            && $this->getMissingFiles() === []
            && $this->getNotWritingDirectories() === [];
    }

    private function checkRootWritable(): bool
    {
        // Root directory permissions cannot be checked recursively anymore, it takes too much time
        return ConfigurationTest::test_dir('/', false, $this->rootWritableReport);
    }

    private function checkModuleVersionIsLastest(Upgrader $upgrader): bool
    {
        return version_compare($this->getModuleVersion(), $upgrader->autoupgrade_last_version, '>=');
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

    /**
     * @return array{"php_min_version": string, "php_max_version": string, "php_current_version": string}|null
     */
    public function getPhpCompatibilityRange(): ?array
    {
        if (null !== $this->phpCompatibilityRange) {
            return $this->phpCompatibilityRange;
        }

        $targetVersion = $this->upgrader->version_num;

        if (null === $targetVersion) {
            return null;
        }

        if (version_compare($targetVersion, '8', '<')) {
            $targetMinorVersion = VersionUtils::splitPrestaShopVersion($targetVersion)['minor'];

            if (!isset($this::PRESTASHOP_17_PHP_REQUIREMENTS[$targetMinorVersion])) {
                return null;
            }

            $range = $this::PRESTASHOP_17_PHP_REQUIREMENTS[$targetMinorVersion];
        } else {
            try {
                $range = $this->distributionApiService->getPhpVersionRequirements($targetVersion);
            } catch (DistributionApiException $apiException) {
                return null;
            }
        }
        $currentPhpVersion = VersionUtils::getHumanReadableVersionOf(PHP_VERSION_ID);
        $range['php_current_version'] = $currentPhpVersion;

        return $this->phpCompatibilityRange = $range;
    }

    /**
     * @param int $currentVersionId
     *
     * @return self::PHP_REQUIREMENTS_*
     */
    public function getPhpRequirementsState($currentVersionId): int
    {
        $phpCompatibilityRange = $this->getPhpCompatibilityRange();

        if (null == $phpCompatibilityRange) {
            return self::PHP_REQUIREMENTS_UNKNOWN;
        }

        $versionMin = VersionUtils::getPhpVersionId($phpCompatibilityRange['php_min_version']);
        $versionMax = VersionUtils::getPhpVersionId($phpCompatibilityRange['php_max_version']);

        $versionMinWithoutPatch = VersionUtils::getPhpMajorMinorVersionId($versionMin);
        $versionMaxWithoutPatch = VersionUtils::getPhpMajorMinorVersionId($versionMax);

        $currentVersion = VersionUtils::getPhpMajorMinorVersionId($currentVersionId);

        if ($currentVersion >= $versionMinWithoutPatch && $currentVersion <= $versionMaxWithoutPatch) {
            return self::PHP_REQUIREMENTS_VALID;
        }

        return self::PHP_REQUIREMENTS_INVALID;
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
        if ($this->upgrader->version_num === null) {
            return true;
        }

        // Check if key is needed on the version we are upgrading to, if lower, not needed
        if (version_compare($this->upgrader->version_num, '8.1.0', '<')) {
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

    public function isPhpSessionsValid(): bool
    {
        return in_array(session_status(), [PHP_SESSION_ACTIVE, PHP_SESSION_NONE], true);
    }

    /**
     * @return array<string>
     */
    public function getMissingFiles(): array
    {
        return ConfigurationTest::test_files(true);
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
}
