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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use PrestaShop\Module\AutoUpgrade\Tools14;
use PrestaShop\Module\AutoUpgrade\UpgradeException;
use PrestaShop\Module\AutoUpgrade\ZipAction;

class ModuleAdapter
{
    private $db;
    private $translator;
    // PS version to update
    private $upgradeVersion;
    private $modulesPath;
    private $tempPath;
    /**
     * @var ZipAction
     */
    private $zipAction;

    /**
     * @var SymfonyAdapter
     */
    private $symfonyAdapter;

    // Cached instance
    private $moduleDataUpdater;

    public function __construct($db, $translator, $modulesPath, $tempPath, $upgradeVersion, ZipAction $zipAction, SymfonyAdapter $symfonyAdapter)
    {
        $this->db = $db;
        $this->translator = $translator;
        $this->modulesPath = $modulesPath;
        $this->tempPath = $tempPath;
        $this->upgradeVersion = $upgradeVersion;
        $this->zipAction = $zipAction;
        $this->symfonyAdapter = $symfonyAdapter;
    }

    /**
     * Available only from 1.7. Can't be called on PS 1.6.
     *
     * @return \PrestaShop\PrestaShop\Adapter\Module\ModuleDataUpdater
     */
    public function getModuleDataUpdater()
    {
        if (null === $this->moduleDataUpdater) {
            $this->moduleDataUpdater = $this->symfonyAdapter
                ->initAppKernel()
                ->getContainer()
                ->get('prestashop.core.module.updater');
        }

        return $this->moduleDataUpdater;
    }

    /**
     * Upgrade action, disabling all modules not made by PrestaShop.
     *
     * It seems the 1.6 version of is the safest, as it does not actually load the modules.
     *
     * @param string $pathToUpgradeScripts Path to the PHP Upgrade scripts
     */
    public function disableNonNativeModules($pathToUpgradeScripts)
    {
        require_once $pathToUpgradeScripts . 'php/deactivate_custom_modules.php';
        deactivate_custom_modules();
    }

    /**
     * list modules to upgrade and save them in a serialized array in $this->toUpgradeModuleList.
     *
     * @param array $modulesFromAddons Modules available on the marketplace for download
     *
     * @return array Module available on the local filesystem and on the marketplace
     */
    public function listModulesToUpgrade(array $modulesFromAddons)
    {
        $list = array();
        $dir = $this->modulesPath;

        if (!is_dir($dir)) {
            throw (new UpgradeException($this->translator->trans('[ERROR] %dir% does not exist or is not a directory.', array('%dir%' => $dir), 'Modules.Autoupgrade.Admin')))
                ->addQuickInfo($this->translator->trans('[ERROR] %s does not exist or is not a directory.', array($dir), 'Modules.Autoupgrade.Admin'))
                ->setSeverity(UpgradeException::SEVERITY_ERROR);
        }

        foreach (scandir($dir) as $module_name) {
            if (is_file($dir . DIRECTORY_SEPARATOR . $module_name)) {
                continue;
            }

            if (!is_file($dir . $module_name . DIRECTORY_SEPARATOR . $module_name . '.php')) {
                continue;
            }
            $id_addons = array_search($module_name, $modulesFromAddons);
            if (false !== $id_addons && $module_name !== 'autoupgrade') {
                $list[] = array('id' => $id_addons, 'name' => $module_name);
            }
        }

        return $list;
    }

    /**
     * Upgrade module $name (identified by $id_module on addons server).
     *
     * @param int $id
     * @param string $name
     */
    public function upgradeModule($id, $name)
    {
        $zip_fullpath = $this->tempPath . DIRECTORY_SEPARATOR . $name . '.zip';

        $addons_url = 'api.addons.prestashop.com';
        $protocolsList = array('https://' => 443, 'http://' => 80);
        if (!extension_loaded('openssl')) {
            unset($protocolsList['https://']);
        } else {
            unset($protocolsList['http://']);
        }

        $postData = 'version=' . $this->upgradeVersion . '&method=module&id_module=' . (int) $id;

        // Make the request
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'content' => $postData,
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'timeout' => 10,
            ),
        );
        $context = stream_context_create($opts);
        foreach ($protocolsList as $protocol => $port) {
            // file_get_contents can return false if https is not supported (or warning)
            $content = Tools14::file_get_contents($protocol . $addons_url, false, $context);
            if ($content == false || substr($content, 5) == '<?xml') {
                continue;
            }

            if (empty($content)) {
                $msg = '<strong>' . $this->translator->trans('[ERROR] No response from Addons server.', array(), 'Modules.Autoupgrade.Admin') . '</strong>';
                throw new UpgradeException($msg);
            }

            if (false === (bool) file_put_contents($zip_fullpath, $content)) {
                $msg = '<strong>' . $this->translator->trans('[ERROR] Unable to write module %s\'s zip file in temporary directory.', array($name), 'Modules.Autoupgrade.Admin') . '</strong>';
                throw new UpgradeException($msg);
            }

            if (filesize($zip_fullpath) <= 300) {
                unlink($zip_fullpath);
            }
            // unzip in modules/[mod name] old files will be conserved
            if (!$this->zipAction->extract($zip_fullpath, $this->modulesPath)) {
                throw (new UpgradeException('<strong>' . $this->translator->trans('[WARNING] Error when trying to extract module %s.', array($name), 'Modules.Autoupgrade.Admin') . '</strong>'))
                    ->setSeverity(UpgradeException::SEVERITY_WARNING);
            }
            if (file_exists($zip_fullpath)) {
                unlink($zip_fullpath);
            }

            // Only 1.7 step
            if (version_compare($this->upgradeVersion, '1.7.0.0', '>=')
                && !$this->doUpgradeModule($name)) {
                throw (new UpgradeException('<strong>' . $this->translator->trans('[WARNING] Error when trying to upgrade module %s.', array($name), 'Modules.Autoupgrade.Admin') . '</strong>'))
                    ->setSeverity(UpgradeException::SEVERITY_WARNING)
                    ->setQuickInfos(\Module::getInstanceByName($name)->getErrors());
            }

            return;
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function doUpgradeModule($name)
    {
        $version = \Db::getInstance()->getValue(
            'SELECT version FROM `' . _DB_PREFIX_ . 'module` WHERE name = "' . $name . '"'
        );
        $module = \Module::getInstanceByName($name);
        $module->installed = !empty($version);
        $module->database_version = $version ?: 0;

        if (\Module::initUpgradeModule($module)) {
            $module->runUpgradeModule();
            \Module::upgradeModuleVersion($name, $module->version);

            return !count($module->getErrors());
        }

        return true;
    }
}
