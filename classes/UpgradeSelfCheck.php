<?php

/**
 * 2007-2017 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade;

use Configuration;
use ConfigurationTest;

class UpgradeSelfCheck
{
    /**
     * @var bool
     */
    private $fOpenOrCurlEnabled;

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
    private $rootWritableReport = '';

    /**
     * @var false|string
     */
    private $moduleVersion;

    /**
     * @var int
     */
    private $maxExecutionTime;

    /**
     * @var bool
     */
    private $prestashopReady;

    /**
     * @var string
     */
    private $configDir = '/modules/autoupgrade/config.xml';

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
        $this->moduleVersion = $this->checkModuleVersion();
        $this->fOpenOrCurlEnabled = ConfigurationTest::test_fopen() || extension_loaded('curl');
        $this->rootDirectoryWritable = $this->checkRootWritable();
        $this->adminAutoUpgradeDirectoryWritable = $this->checkAdminDirectoryWritable($prodRootPath, $adminPath, $autoUpgradePath);
        $this->shopDeactivated = $this->checkShopIsDeactivated();
        $this->cacheDisabled = !(defined('_PS_CACHE_ENABLED_') && _PS_CACHE_ENABLED_);
        $this->safeModeDisabled = $this->checkSafeModeIsDisabled();
        $this->moduleVersionIsLatest = $this->checkModuleVersionIsLastest($upgrader);
        $this->maxExecutionTime = $this->checkMaxExecutionTime();
        $this->prestashopReady = $this->runPrestaShopCoreChecks();
    }

    /**
     * @return bool
     */
    public function isFOpenOrCurlEnabled()
    {
        return $this->fOpenOrCurlEnabled;
    }

    /**
     * @return bool
     */
    public function isRootDirectoryWritable()
    {
        return $this->rootDirectoryWritable;
    }

    /**
     * @return bool
     */
    public function isAdminAutoUpgradeDirectoryWritable()
    {
        return $this->adminAutoUpgradeDirectoryWritable;
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
    public function isShopDeactivated()
    {
        return $this->shopDeactivated;
    }

    /**
     * @return bool
     */
    public function isCacheDisabled()
    {
        return $this->cacheDisabled;
    }

    /**
     * @return bool
     */
    public function isSafeModeDisabled()
    {
        return $this->safeModeDisabled;
    }

    /**
     * @return bool
     */
    public function isModuleVersionLatest()
    {
        return $this->moduleVersionIsLatest;
    }

    /**
     * @return string
     */
    public function getRootWritableReport()
    {
        return $this->rootWritableReport;
    }

    /**
     * @return string|false
     */
    public function getModuleVersion()
    {
        return $this->moduleVersion;
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
        return $this->maxExecutionTime;
    }

    public function isPrestaShopReady()
    {
        return $this->prestashopReady || 1 === Configuration::get('PS_AUTOUP_IGNORE_REQS');
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
            && $this->isRootDirectoryWritable()
            && $this->isAdminAutoUpgradeDirectoryWritable()
            && $this->isShopDeactivated()
            && $this->isCacheDisabled()
            && $this->isModuleVersionLatest()
            && $this->isPrestaShopReady()
        ;
    }

    /**
     * @return bool
     */
    private function checkRootWritable()
    {
        // Root directory permissions cannot be checked recursively anymore, it takes too much time
        return  ConfigurationTest::test_dir('/', false, $this->rootWritableReport);
    }

    /**
     * @param Upgrader $upgrader
     *
     * @return bool
     */
    private function checkModuleVersionIsLastest(Upgrader $upgrader)
    {
        return version_compare($this->moduleVersion, $upgrader->autoupgrade_last_version, '>=');
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
     * @return bool
     */
    private function checkShopIsDeactivated()
    {
        return
            !Configuration::get('PS_SHOP_ENABLE')
            || (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], array('127.0.0.1', 'localhost')))
        ;
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

        return !in_array(strtolower($safeMode), array(1, 'on'));
    }

    /**
     * @return int
     */
    private function checkMaxExecutionTime()
    {
        return (int) @ini_get('max_execution_time');
    }

    /**
     * Ask the core to run its tests, if available.
     *
     * @return bool
     */
    public function runPrestaShopCoreChecks()
    {
        if (!class_exists('ConfigurationTest')) {
            return true;
        }

        $defaultTests = ConfigurationTest::check(ConfigurationTest::getDefaultTests());
        foreach ($defaultTests as $testResult) {
            if ($testResult !== 'ok') {
                return false;
            }
        }

        return true;
    }
}
