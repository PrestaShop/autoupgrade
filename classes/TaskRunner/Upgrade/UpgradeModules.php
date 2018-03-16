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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade;

use PrestaShop\Module\AutoUpgrade\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
/**
 * Upgrade all partners modules according to the installed prestashop version
 */
class UpgradeModules extends AbstractTask
{
    public function run()
    {
        $start_time = time();
        if (!isset($this->upgradeClass->nextParams['modulesToUpgrade'])) {
            try {
                $modulesToUpgrade = $this->upgradeClass->getModuleAdapter()->listModulesToUpgrade($this->upgradeClass->getState()-> getModules_addons());
                $this->upgradeClass->getFileConfigurationStorage()->save($modulesToUpgrade, UpgradeFileNames::toUpgradeModuleList);
                $this->upgradeClass->nextParams['modulesToUpgrade'] = UpgradeFileNames::toUpgradeModuleList;
            } catch (UpgradeException $e) {
                $this->upgradeClass->handleException($e);
                return false;
            }

            $total_modules_to_upgrade = count($modulesToUpgrade);
            if ($total_modules_to_upgrade) {
                $this->logger->info($this->upgradeClass->getTranslator()->trans('%s modules will be upgraded.', array($total_modules_to_upgrade), 'Modules.Autoupgrade.Admin'));
            }
            $this->upgradeClass->stepDone = false;
            $this->upgradeClass->next = 'upgradeModules';
            return true;
        }

        $this->upgradeClass->next = 'upgradeModules';
        $listModules = $this->upgradeClass->getFileConfigurationStorage()->load($this->upgradeClass->nextParams['modulesToUpgrade']);

        if (!is_array($listModules)) {
            $this->upgradeClass->next = 'upgradeComplete';
            $this->upgradeClass->getState()-> setWarningExists(true);
            $this->logger->error($this->upgradeClass->getTranslator()->trans('listModules is not an array. No module has been updated.', array(), 'Modules.Autoupgrade.Admin'));
            return true;
        }

        $time_elapsed = time() - $start_time;
        // module list
        if (count($listModules) > 0) {
            do {
                $module_info = array_shift($listModules);
                try {
                    $this->upgradeClass->getModuleAdapter()->upgradeModule($module_info['id'], $module_info['name']);
                    $this->logger->debug($this->upgradeClass->getTranslator()->trans('The files of module %s have been upgraded.', array($module_info['name']), 'Modules.Autoupgrade.Admin'));
                } catch (UpgradeException $e) {
                    $this->upgradeClass->handleException($e);
                }
                $time_elapsed = time() - $start_time;
            } while (($time_elapsed < \AdminSelfUpgrade::$loopUpgradeModulesTime) && count($listModules) > 0);

            $modules_left = count($listModules);
            $this->upgradeClass->getFileConfigurationStorage()->save($listModules, UpgradeFileNames::toUpgradeModuleList);
            unset($listModules);

            $this->upgradeClass->next = 'upgradeModules';
            if ($modules_left) {
                $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('%s modules left to upgrade.', array($modules_left), 'Modules.Autoupgrade.Admin');
            }
            $this->upgradeClass->stepDone = false;
        } else {
            $modules_to_delete = array(
                'backwardcompatibility' => 'Backward Compatibility',
                'dibs' => 'Dibs',
                'cloudcache' => 'Cloudcache',
                'mobile_theme' => 'The 1.4 mobile_theme',
                'trustedshops' => 'Trustedshops',
                'dejala' => 'Dejala',
                'stripejs' => 'Stripejs',
                'blockvariouslinks' => 'Block Various Links',
            );

            foreach ($modules_to_delete as $key => $module) {
                $this->upgradeClass->db->execute('DELETE ms.*, hm.*
                FROM `'._DB_PREFIX_.'module_shop` ms
                INNER JOIN `'._DB_PREFIX_.'hook_module` hm USING (`id_module`)
                INNER JOIN `'._DB_PREFIX_.'module` m USING (`id_module`)
                WHERE m.`name` LIKE \''.pSQL($key).'\'');
                $this->upgradeClass->db->execute('UPDATE `'._DB_PREFIX_.'module` SET `active` = 0 WHERE `name` LIKE \''.pSQL($key).'\'');

                $path = $this->upgradeClass->prodRootDir.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$key.DIRECTORY_SEPARATOR;
                if (file_exists($path.$key.'.php')) {
                    if (\AdminSelfUpgrade::deleteDirectory($path)) {
                        $this->logger->debug($this->upgradeClass->getTranslator()->trans(
                            'The %modulename% module is not compatible with version %version%, it will be removed from your FTP.',
                            array(
                                '%modulename%' => $module,
                                '%version%' => $this->upgradeClass->getState()-> getInstallVersion(),
                            ),
                            'Modules.Autoupgrade.Admin'
                        ));
                    } else {
                        $this->logger->error($this->upgradeClass->getTranslator()->trans(
                            'The %modulename% module is not compatible with version %version%, please remove it from your FTP.',
                            array(
                                '%modulename%' => $module,
                                '%version%' => $this->upgradeClass->getState()-> getInstallVersion(),
                            ),
                            'Modules.Autoupgrade.Admin'
                        ));
                    }
                }
            }

            $this->upgradeClass->stepDone = true;
            $this->upgradeClass->status = 'ok';
            $this->upgradeClass->next = 'cleanDatabase';
            $this->logger->info($this->upgradeClass->getTranslator()->trans('Addons modules files have been upgraded.', array(), 'Modules.Autoupgrade.Admin'));
            if ($this->upgradeClass->manualMode) {
                $this->upgradeClass->writeConfig(array('PS_AUTOUP_MANUAL_MODE' => '0'));
            }
            return true;
        }
        return true;
    }
}