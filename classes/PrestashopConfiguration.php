<?php

/*
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade;

use ConfigurationTestCore as ConfigurationTest;
use Configuration;

class PrestashopConfiguration
{
    // Variables used for cache
    private $moduleVersion = null;
    private $allowed_array = array();

    // Variables from main class
    private $autoupgradeDir;
    private $psRootDir;

    public function __construct($moduleDir, $psRootDir)
    {
        $this->autoupgradeDir = $moduleDir;
        $this->psRootDir = $psRootDir;
    }

    /**
     * @return bool True if all checks are true
     */
    public function isCompliant()
    {
        return array_product($this->getCompliancyResults());
    }

    /**
     * @return array of compliancy checks
     */
    public function getCompliancyResults()
    {
        if (!count($this->allowed_array)) {
            $this->allowed_array = array_merge(
                $this->getRootWritableDetails(),
                array(
                    'fopen' => (ConfigurationTest::test_fopen() || ConfigurationTest::test_curl()),
                    'admin_au_writable' => ConfigurationTest::test_dir($this->autoupgradeDir, false),
                    'shop_deactivated' => (!Configuration::get('PS_SHOP_ENABLE') || (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], array('127.0.0.1', 'localhost')))),
                    'cache_deactivated' => !(defined('_PS_CACHE_ENABLED_') && _PS_CACHE_ENABLED_),
                    'module_version_ok' => $this->checkAutoupgradeLastVersion($this->getUpgrader()->autoupgrade_last_version),
            ));
        }

        return $this->allowed_array;
    }

    /**
     * @return string|false Returns the module version if found in the config.xml file, false otherwise.
     */
    public function getModuleVersion()
    {
        if (!is_null($this->moduleVersion)) {
            return $this->moduleVersion;
        }

        // TODO: to be moved as property class in order to make tests possible
        $path = _PS_ROOT_DIR_ . '/modules/autoupgrade/config.xml';

        $this->moduleVersion = false;
        if (file_exists($path)
            && $xml_module_version = simplexml_load_file($path)
        ) {
            $this->moduleVersion = (string) $xml_module_version->version;
        }

        return $this->moduleVersion;
    }

    public function getPrestaShopVersion()
    {
        if (defined('_PS_VERSION_')) {
            return _PS_VERSION_;
        }
        $files = array(
            $this->psRootDir . '/config/settings.inc.php',
            $this->psRootDir . '/config/autoload.php',
        );
        foreach ($files as $file) {
            $version = $this->findPrestaShopVersionInFile(file_get_contents($file));
            if ($version) {
                return $version;
            }
        }

        throw new \Exception('Can\'t find PrestaShop Version');
    }

    /**
     * Compares the installed module version with the one available on download.
     *
     * @return bool True is the latest version of the module is currently installed
     */
    public function checkAutoupgradeLastVersion($extAutoupgradeLastVersion)
    {
        $moduleVersion = $this->getModuleVersion();
        if ($moduleVersion) {
            return version_compare($moduleVersion, $extAutoupgradeLastVersion, '>=');
        }

        return true;
    }

    /**
     * @return array Details of the filesystem permission check
     */
    protected function getRootWritableDetails()
    {
        $result = array();
        // Root directory permissions cannot be checked recursively anymore, it takes too much time
        $result['root_writable'] = ConfigurationTest::test_dir('/', false, $report);
        $result['root_writable_report'] = $report ? $report : true; // Avoid null in the array as it makes the shop non-compliant

        return $result;
    }

    /**
     * @param type $content
     *
     * @return bool|string
     *
     * @internal Used for test
     */
    public function findPrestaShopVersionInFile($content)
    {
        $matches = array();
        if (1 === preg_match("/define\([\"']_PS_VERSION_[\"'], [\"'](?<version>[0-9.]+)[\"']\)/", $content, $matches)) {
            return $matches['version'];
        }

        return false;
    }
}
