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
use Shop;

class UpgradeSelfCheck
{
    /**
     * Recommended PHP Version. If below, display a notice.
     */
    const RECOMMENDED_PHP_VERSION = 70205;

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
     * @var false|string
     */
    private $moduleVersion;

    /**
     * @var int
     */
    private $maxExecutionTime;

    /**
     * Warning flag for an old running PHP server.
     *
     * @var bool
     */
    private $phpUpgradeNoticelink;

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
     * @var bool
     */
    private $overrideDisabled;

    /**
     * UpgradeSelfCheck constructor.
     *
     * @param Upgrader $upgrader
     * @param string $prodRootPath
     * @param string $adminPath
     * @param string $autoUpgradePath
     */
    public function __construct(Upgrader $upgrader, $prodRootPath, $adminPath, $autoUpgradePath)
    {
        $this->upgrader = $upgrader;
        $this->prodRootPath = $prodRootPath;
        $this->adminPath = $adminPath;
        $this->autoUpgradePath = $autoUpgradePath;
    }

    /**
     * @return bool
     */
    public function isFOpenOrCurlEnabled()
    {
        if (null !== $this->fOpenOrCurlEnabled) {
            return $this->fOpenOrCurlEnabled;
        }

        return $this->fOpenOrCurlEnabled = ConfigurationTest::test_fopen() || extension_loaded('curl');
    }

    /**
     * @return bool
     */
    public function isZipEnabled()
    {
        if (null !== $this->zipEnabled) {
            return $this->zipEnabled;
        }

        return $this->zipEnabled = extension_loaded('zip');
    }

    /**
     * @return bool
     */
    public function isRootDirectoryWritable()
    {
        if (null !== $this->rootDirectoryWritable) {
            return $this->rootDirectoryWritable;
        }

        return $this->rootDirectoryWritable = $this->checkRootWritable();
    }

    /**
     * @return bool
     */
    public function isAdminAutoUpgradeDirectoryWritable()
    {
        if (null !== $this->adminAutoUpgradeDirectoryWritable) {
            return $this->adminAutoUpgradeDirectoryWritable;
        }

        return $this->adminAutoUpgradeDirectoryWritable = $this->checkAdminDirectoryWritable($this->prodRootPath, $this->adminPath, $this->autoUpgradePath);
    }

    /**
     * @return string
     */
    public function getAdminAutoUpgradeDirectoryWritableReport()
    {
        return $this->adminAutoUpgradeDirectoryWritableReport;
    }

    /**
     * @return bool
     */
    public function isOverrideDisabled()
    {
        if (null === $this->overrideDisabled) {
            $this->overrideDisabled = $this->checkOverrideIsDisabled();
        }

        return $this->overrideDisabled;
    }

    /**
     * @return bool
     */
    public function isShopDeactivated()
    {
        if (null !== $this->shopDeactivated) {
            return $this->shopDeactivated;
        }

        return $this->shopDeactivated = $this->checkShopIsDeactivated();
    }

    /**
     * @return bool
     */
    public function isCacheDisabled()
    {
        if (null !== $this->cacheDisabled) {
            return $this->cacheDisabled;
        }

        return $this->cacheDisabled = !(defined('_PS_CACHE_ENABLED_') && false != _PS_CACHE_ENABLED_);
    }

    /**
     * @return bool
     */
    public function isSafeModeDisabled()
    {
        if (null !== $this->safeModeDisabled) {
            return $this->safeModeDisabled;
        }

        return $this->safeModeDisabled = $this->checkSafeModeIsDisabled();
    }

    /**
     * @return bool
     */
    public function isModuleVersionLatest()
    {
        if (null !== $this->moduleVersionIsLatest) {
            return $this->moduleVersionIsLatest;
        }

        return $this->moduleVersionIsLatest = $this->checkModuleVersionIsLastest($this->upgrader);
    }

    /**
     * @return string
     */
    public function getRootWritableReport()
    {
        if (null !== $this->rootWritableReport) {
            return $this->rootWritableReport;
        }

        $this->rootWritableReport = '';
        $this->isRootDirectoryWritable();

        return $this->rootWritableReport;
    }

    /**
     * @return string|false
     */
    public function getModuleVersion()
    {
        if (null !== $this->moduleVersion) {
            return $this->moduleVersion;
        }

        return $this->moduleVersion = $this->checkModuleVersion();
    }

    /**
     * @return string
     */
    public function getConfigDir()
    {
        return $this->configDir;
    }

    /**
     * @return int
     */
    public function getMaxExecutionTime()
    {
        if (null !== $this->maxExecutionTime) {
            return $this->maxExecutionTime;
        }

        return $this->maxExecutionTime = $this->checkMaxExecutionTime();
    }

    /**
     * @return bool
     */
    public function isPhpUpgradeRequired()
    {
        if (null !== $this->phpUpgradeNoticelink) {
            return $this->phpUpgradeNoticelink;
        }

        return $this->phpUpgradeNoticelink = $this->checkPhpVersionNeedsUpgrade();
    }

    /**
     * Indicates if the self check status allows going ahead with the upgrade.
     *
     * @return bool
     */
    public function isOkForUpgrade()
    {
        return
            $this->isFOpenOrCurlEnabled()
            && $this->isZipEnabled()
            && $this->isRootDirectoryWritable()
            && $this->isAdminAutoUpgradeDirectoryWritable()
            && $this->isShopDeactivated()
            && $this->isCacheDisabled()
            && $this->isModuleVersionLatest()
            && $this->isPhpVersionCompatible()
            && $this->isApacheModRewriteEnabled()
            && $this->getNotLoadedPhpExtensions() === []
            && $this->isMemoryLimitValid()
            && $this->isPhpFileUploadsConfigurationEnabled()
            && $this->getNotExistsPhpFunctions() === []
            && $this->isPhpSessionsValid()
            && $this->getMissingFiles() === []
            && $this->getNotWritingDirectories() === []
        ;
    }

    /**
     * @return bool
     */
    private function checkRootWritable()
    {
        // Root directory permissions cannot be checked recursively anymore, it takes too much time
        return ConfigurationTest::test_dir('/', false, $this->rootWritableReport);
    }

    /**
     * @param Upgrader $upgrader
     *
     * @return bool
     */
    private function checkModuleVersionIsLastest(Upgrader $upgrader)
    {
        return version_compare($this->getModuleVersion(), $upgrader->autoupgrade_last_version, '>=');
    }

    /**
     * @return string|false
     */
    private function checkModuleVersion()
    {
        $configFilePath = _PS_ROOT_DIR_ . $this->configDir;

        if (file_exists($configFilePath) && $xml_module_version = simplexml_load_file($configFilePath)) {
            return (string) $xml_module_version->version;
        }

        return false;
    }

    /**
     * Check current PHP version is supported.
     *
     * @return bool
     */
    private function checkPhpVersionNeedsUpgrade()
    {
        return PHP_VERSION_ID < self::RECOMMENDED_PHP_VERSION;
    }

    /**
     * @return bool
     */
    private function checkOverrideIsDisabled()
    {
        return (bool) Configuration::get('PS_DISABLE_OVERRIDES');
    }

    /**
     * @return bool
     */
    private function checkShopIsDeactivated()
    {
        // always return true in localhost
        if (in_array($this->getRemoteAddr(), ['127.0.0.1', 'localhost', '[::1]', '::1'])) {
            return true;
        }

        // if multistore is not active, just check if shop is enabled and has a maintenance IP
        if (!Shop::isFeatureActive()) {
            return !(Configuration::get('PS_SHOP_ENABLE') || !Configuration::get('PS_MAINTENANCE_IP'));
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

    /**
     * @param string $prodRootPath
     * @param string $adminPath
     * @param string $adminAutoUpgradePath
     *
     * @return bool
     */
    private function checkAdminDirectoryWritable($prodRootPath, $adminPath, $adminAutoUpgradePath)
    {
        $relativeDirectory = trim(str_replace($prodRootPath, '', $adminAutoUpgradePath), DIRECTORY_SEPARATOR);

        return ConfigurationTest::test_dir(
            $relativeDirectory,
            false,
            $this->adminAutoUpgradeDirectoryWritableReport
        );
    }

    /**
     * @return bool
     */
    private function checkSafeModeIsDisabled()
    {
        $safeMode = @ini_get('safe_mode');
        if (empty($safeMode)) {
            $safeMode = '';
        }

        return !in_array(strtolower($safeMode), [1, 'on']);
    }

    /**
     * @return int
     */
    private function checkMaxExecutionTime()
    {
        return (int) @ini_get('max_execution_time');
    }

    /**
     * @return bool
     */
    public function isPhpVersionCompatible()
    {
        if (!class_exists(ConfigurationTest::class)) {
            return true;
        }

        return (bool) ConfigurationTest::test_phpversion();
    }

    /**
     * @return bool
     */
    public function isApacheModRewriteEnabled()
    {
        if (class_exists(ConfigurationTest::class) && is_callable([ConfigurationTest::class, 'test_apache_mod_rewrite'])) {
            return ConfigurationTest::test_apache_mod_rewrite();
        }

        return true;
    }

    /**
     * @return array<string>
     */
    public function getNotLoadedPhpExtensions()
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
    public function getNotExistsPhpFunctions()
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
    public function isMemoryLimitValid()
    {
        if (class_exists(ConfigurationTest::class) && is_callable([ConfigurationTest::class, 'test_memory_limit'])) {
            return ConfigurationTest::test_memory_limit();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isPhpFileUploadsConfigurationEnabled()
    {
        if (!class_exists(ConfigurationTest::class)) {
            return true;
        }

        return (bool) ConfigurationTest::test_upload();
    }

    /**
     * @return bool
     */
    public function isPhpSessionsValid()
    {
        if (!class_exists(ConfigurationTest::class)) {
            return true;
        }

        return ConfigurationTest::test_sessions();
    }

    /**
     * @return array<string>
     */
    public function getMissingFiles()
    {
        return ConfigurationTest::test_files(true);
    }

    /**
     * @return array<string>
     */
    public function getNotWritingDirectories()
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
    private function getRemoteAddr()
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
