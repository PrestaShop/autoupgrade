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

use ConfigurationTestCore as ConfigurationTest;

class PrestashopConfiguration
{
    // Variables used for cache
    private $moduleVersion;
    private $allowed_array = [];

    // Variables from main class
    private $autoupgradeDir;
    private $psRootDir;

    public function __construct($moduleDir, $psRootDir)
    {
        $this->autoupgradeDir = $moduleDir;
        $this->psRootDir = $psRootDir;
    }

    /**
     * @return string|false Returns the module version if found in the config.xml file, false otherwise.
     */
    public function getModuleVersion()
    {
        if (null !== $this->moduleVersion) {
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
        $files = [
            $this->psRootDir . '/config/settings.inc.php',
            $this->psRootDir . '/config/autoload.php',
            $this->psRootDir . '/app/AppKernel.php',
        ];
        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }
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
        $result = [];
        // Root directory permissions cannot be checked recursively anymore, it takes too much time
        $result['root_writable'] = ConfigurationTest::test_dir('/', false, $report);
        $result['root_writable_report'] = $report ? $report : true; // Avoid null in the array as it makes the shop non-compliant

        return $result;
    }

    /**
     * @param string $content File content
     *
     * @return bool|string
     *
     * @internal Used for test
     */
    public function findPrestaShopVersionInFile($content)
    {
        $matches = [];
        // Example: define('_PS_VERSION_', '1.7.3.4');
        if (1 === preg_match("/define\([\"']_PS_VERSION_[\"'], [\"'](?<version>[0-9.]+)[\"']\)/", $content, $matches)) {
            return $matches['version'];
        }

        // Example: const VERSION = '1.7.6.0';
        if (1 === preg_match("/const VERSION = [\"'](?<version>[0-9.]+)[\"'];/", $content, $matches)) {
            return $matches['version'];
        }

        return false;
    }
}
