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

use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;

class UpgradeFiles extends AbstractTask
{
    public function run()
    {
        $this->upgradeClass->nextParams = $this->upgradeClass->currentParams;

        $admin_dir = str_replace($this->upgradeClass->prodRootDir.DIRECTORY_SEPARATOR, '', $this->upgradeClass->adminDir);
        if (file_exists($this->upgradeClass->latestRootDir.DIRECTORY_SEPARATOR.'admin')) {
            rename($this->upgradeClass->latestRootDir.DIRECTORY_SEPARATOR.'admin', $this->upgradeClass->latestRootDir.DIRECTORY_SEPARATOR.$admin_dir);
        } elseif (file_exists($this->upgradeClass->latestRootDir.DIRECTORY_SEPARATOR.'admin-dev')) {
            rename($this->upgradeClass->latestRootDir.DIRECTORY_SEPARATOR.'admin-dev', $this->upgradeClass->latestRootDir.DIRECTORY_SEPARATOR.$admin_dir);
        }
        if (file_exists($this->upgradeClass->latestRootDir.DIRECTORY_SEPARATOR.'install-dev')) {
            rename($this->upgradeClass->latestRootDir.DIRECTORY_SEPARATOR.'install-dev', $this->upgradeClass->latestRootDir.DIRECTORY_SEPARATOR.'install');
        }

        if (!$this->upgradeClass->getFileConfigurationStorage()->exists(UpgradeFileNames::toUpgradeFileList)) {
            // list saved in UpgradeFileNames::toUpgradeFileList
            // get files differences (previously generated)
            $admin_dir = trim(str_replace($this->upgradeClass->prodRootDir, '', $this->upgradeClass->adminDir), DIRECTORY_SEPARATOR);
            $filepath_list_diff = $this->upgradeClass->autoupgradePath.DIRECTORY_SEPARATOR.UpgradeFileNames::diffFileList;
            $list_files_diff = array();
            if (file_exists($filepath_list_diff)) {
                $list_files_diff = $this->upgradeClass->getFileConfigurationStorage()->load(UpgradeFileNames::diffFileList);
                // only keep list of files to delete. The modified files will be listed with _listFilesToUpgrade
                $list_files_diff = $list_files_diff['deleted'];
                foreach ($list_files_diff as $k => $path) {
                    if (preg_match("#autoupgrade#", $path)) {
                        unset($list_files_diff[$k]);
                    } else {
                        $list_files_diff[$k] = str_replace('/'.'admin', '/'.$admin_dir, $path);
                    }
                } // do not replace by DIRECTORY_SEPARATOR
            }

            if (!($list_files_to_upgrade = $this->upgradeClass->_listFilesToUpgrade($this->upgradeClass->latestRootDir))) {
                return false;
            }

            // also add files to remove
            $list_files_to_upgrade = array_merge($list_files_diff, $list_files_to_upgrade);

            $filesToMoveToTheBeginning = array(
                DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php',
                DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.'ClassLoader.php',
                DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.'autoload_classmap.php',
                DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.'autoload_files.php',
                DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.'autoload_namespaces.php',
                DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.'autoload_psr4.php',
                DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.'autoload_real.php',
                DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.'autoload_static.php',
                DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.'include_paths.php',
            );

            foreach ($filesToMoveToTheBeginning as $file) {
                if ($key = array_search($file, $list_files_to_upgrade)) {
                    unset($list_files_to_upgrade[$key]);
                    $list_files_to_upgrade = array_merge(array($file), $list_files_to_upgrade);
                }
            }

            // save in a serialized array in UpgradeFileNames::toUpgradeFileList
            $this->upgradeClass->getFileConfigurationStorage()->save($list_files_to_upgrade, UpgradeFileNames::toUpgradeFileList);
            $this->upgradeClass->nextParams['filesToUpgrade'] = UpgradeFileNames::toUpgradeFileList;
            $total_files_to_upgrade = count($list_files_to_upgrade);

            if ($total_files_to_upgrade == 0) {
                $this->logger->error($this->upgradeClass->getTranslator()->trans('[ERROR] Unable to find files to upgrade.', array(), 'Modules.Autoupgrade.Admin'));
                $this->upgradeClass->next = 'error';
                return false;
            }
            $this->logger->info($this->upgradeClass->getTranslator()->trans('%s files will be upgraded.', array($total_files_to_upgrade), 'Modules.Autoupgrade.Admin'));
            $this->upgradeClass->next = 'upgradeFiles';
            $this->upgradeClass->stepDone = false;
            return true;
        }

        // later we could choose between _PS_ROOT_DIR_ or _PS_TEST_DIR_
        $this->upgradeClass->destUpgradePath = $this->upgradeClass->prodRootDir;

        $this->upgradeClass->next = 'upgradeFiles';
        $filesToUpgrade = $this->upgradeClass->getFileConfigurationStorage()->load(UpgradeFileNames::toUpgradeFileList);
        if (!is_array($filesToUpgrade)) {
            $this->upgradeClass->next = 'error';
            $this->logger->error($this->upgradeClass->getTranslator()->trans('filesToUpgrade is not an array', array(), 'Modules.Autoupgrade.Admin'));
            return false;
        }

        // @TODO : does not upgrade files in modules, translations if they have not a correct md5 (or crc32, or whatever) from previous version
        for ($i = 0; $i < \AdminSelfUpgrade::$loopUpgradeFiles; $i++) {
            if (count($filesToUpgrade) <= 0) {
                $this->upgradeClass->next = 'upgradeDb';
                if (file_exists(UpgradeFileNames::toUpgradeFileList)) {
                    unlink(UpgradeFileNames::toUpgradeFileList);
                }
                $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('All files upgraded. Now upgrading database...', array(), 'Modules.Autoupgrade.Admin');
                $this->upgradeClass->stepDone = true;
                break;
            }

            $file = array_shift($filesToUpgrade);
            if (!$this->upgradeClass->upgradeThisFile($file)) {
                // put the file back to the begin of the list
                $this->upgradeClass->next = 'error';
                $this->logger->error($this->upgradeClass->getTranslator()->trans('Error when trying to upgrade file %s.', array($file), 'Modules.Autoupgrade.Admin'));
                break;
            }
        }
        $this->upgradeClass->getFileConfigurationStorage()->save($filesToUpgrade, UpgradeFileNames::toUpgradeFileList);
        if (count($filesToUpgrade) > 0) {
            $this->logger->info($this->upgradeClass->getTranslator()->trans('%s files left to upgrade.', array(count($filesToUpgrade)), 'Modules.Autoupgrade.Admin'));
            $this->upgradeClass->stepDone = false;
            @unlink(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR. 'app'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'dev'.DIRECTORY_SEPARATOR.'class_index.php');
            @unlink(_PS_ROOT_DIR_.DIRECTORY_SEPARATOR. 'app'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'prod'.DIRECTORY_SEPARATOR.'class_index.php');
        }
        return true;
    }
}