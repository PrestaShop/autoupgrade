<?php
/**
 * 2007-2019 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
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
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\Module;

use Db;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Disabler is used to disable/enable modules, there are two ways of
 * disabling a module:
 *  - move its whole folder somewhere else
 *  - update the database to set its active column to 0
 */
class Disabler
{
    /** @var Db */
    private $db;

    /** @var string */
    private $modulesDir;

    /** @var string */
    private $disabledModulesDir;

    /** @var Filesystem */
    private $fileSystem;

    /**
     * @param Db $db
     * @param Filesystem $fileSystem
     * @param string $modulesDir
     * @param string $disabledModulesDir
     */
    public function __construct(
        $db,
        Filesystem $fileSystem,
        $modulesDir,
        $disabledModulesDir
    ) {
        $this->db = $db;
        $this->fileSystem = $fileSystem;
        $this->modulesDir = $modulesDir;
        $this->disabledModulesDir = $disabledModulesDir;
    }

    /**
     * @param string $moduleName
     *
     * @return bool
     */
    public function disableModuleFromDatabase($moduleName)
    {
        $moduleId = $this->db->getValue('SELECT id_module FROM `' . _DB_PREFIX_ . 'module` WHERE `name` = "' . pSQL($moduleName) . '"');
        if (!$moduleId) {
            return false;
        }

        $return = $this->db->execute('UPDATE `' . _DB_PREFIX_ . 'module` SET `active` = 0 WHERE `id_module` = ' . (int) $moduleId);
        if (count($this->db->executeS('SHOW TABLES LIKE \'' . _DB_PREFIX_ . 'module_shop\'')) > 0) {
            $return &= Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'module_shop` WHERE `id_module` = ' . (int) $moduleId);
        }

        return $return;
    }

    /**
     * @param string $moduleName
     *
     * @return bool
     */
    public function enableModuleFromDatabase($moduleName)
    {
        $moduleId = $this->db->getValue('SELECT id_module FROM `' . _DB_PREFIX_ . 'module` WHERE `name` = "' . pSQL($moduleName) . '"');
        if (!$moduleId) {
            return false;
        }

        return $this->db->execute('UPDATE `' . _DB_PREFIX_ . 'module` SET `active` = 0 WHERE `id_module` = ' . (int) $moduleId);
    }

    /**
     * @param string $moduleName
     *
     * @return bool
     */
    public function disableModuleFromDisk($moduleName)
    {
        $enabledModulePath = $this->modulesDir . DIRECTORY_SEPARATOR . $moduleName;
        $disabledModulePath = $this->disabledModulesDir . DIRECTORY_SEPARATOR . $moduleName;

        return $this->moveModule($enabledModulePath, $disabledModulePath);
    }

    /**
     * @param string $moduleName
     *
     * @return bool
     */
    public function enableModuleFromDisk($moduleName)
    {
        $enabledModulePath = $this->modulesDir . DIRECTORY_SEPARATOR . $moduleName;
        $disabledModulePath = $this->disabledModulesDir . DIRECTORY_SEPARATOR . $moduleName;

        return $this->moveModule($disabledModulePath, $enabledModulePath);
    }

    /**
     * @param string $originModulePath
     * @param string $targetModulePath
     *
     * @return bool
     */
    private function moveModule($originModulePath, $targetModulePath)
    {
        if (!is_dir($originModulePath)) {
            return false;
        }

        if (!is_dir($this->disabledModulesDir)) {
            $this->fileSystem->mkdir($this->disabledModulesDir, 0755);
        }

        try {
            $this->fileSystem->rename($originModulePath, $targetModulePath, true);
        } catch (IOException $e) {
            return false;
        }

        return true;
    }
}
