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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Rollback;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;

/**
 * ajaxProcessRestoreFiles restore the previously saved files,
 * and delete files that weren't archived
 */
class RestoreFiles extends AbstractTask
{
    public function run()
    {
        // loop
        $this->upgradeClass->next = 'restoreFiles';
        if (!file_exists($this->upgradeClass->autoupgradePath.DIRECTORY_SEPARATOR.UpgradeFileNames::fromArchiveFileList)
            || !file_exists($this->upgradeClass->autoupgradePath.DIRECTORY_SEPARATOR.UpgradeFileNames::toRemoveFileList)) {
            // cleanup current PS tree
            $fromArchive = $this->upgradeClass->getZipAction()->listContent($this->upgradeClass->backupPath.DIRECTORY_SEPARATOR.$this->upgradeClass->getState()-> getRestoreFilesFilename());
            foreach ($fromArchive as $k => $v) {
                $fromArchive[DIRECTORY_SEPARATOR.$v] = DIRECTORY_SEPARATOR.$v;
            }

            $this->upgradeClass->getFileConfigurationStorage()->save($fromArchive, UpgradeFileNames::fromArchiveFileList);
            // get list of files to remove
            $toRemove = $this->upgradeClass->getFilesystemAdapter()->listFilesToRemove();
            $toRemoveOnly = array();

            // let's reverse the array in order to make possible to rmdir
            // remove fullpath. This will be added later in the loop.
            // we do that for avoiding fullpath to be revealed in a text file
            foreach ($toRemove as $k => $v) {
                $vfile = str_replace($this->upgradeClass->prodRootDir, '', $v);
                $toRemove[] = str_replace($this->upgradeClass->prodRootDir, '', $vfile);

                if (!isset($fromArchive[$vfile]) && is_file($v)) {
                    $toRemoveOnly[$vfile] = str_replace($this->upgradeClass->prodRootDir, '', $vfile);
                }
            }

            $this->logger->debug($this->upgradeClass->getTranslator()->trans('%s file(s) will be removed before restoring the backup files.', array(count($toRemoveOnly)), 'Modules.Autoupgrade.Admin'));
            $this->upgradeClass->getFileConfigurationStorage()->save($toRemoveOnly, UpgradeFileNames::toRemoveFileList);

            if ($fromArchive === false || $toRemove === false) {
                if (!$fromArchive) {
                    $this->logger->error($this->upgradeClass->getTranslator()->trans('[ERROR] Backup file %s does not exist.', array(UpgradeFileNames::fromArchiveFileList), 'Modules.Autoupgrade.Admin'));
                }
                if (!$toRemove) {
                    $this->logger->error($this->upgradeClass->getTranslator()->trans('[ERROR] File "%s" does not exist.', array(UpgradeFileNames::toRemoveFileList), 'Modules.Autoupgrade.Admin'));
                }
                $this->logger->info($this->upgradeClass->getTranslator()->trans('Unable to remove upgraded files.', array(), 'Modules.Autoupgrade.Admin'));
                $this->upgradeClass->next = 'error';
                return false;
            }
        }

        if (!empty($fromArchive)) {
            $filepath = $this->upgradeClass->backupPath.DIRECTORY_SEPARATOR.$this->upgradeClass->getState()-> getRestoreFilesFilename();
            $destExtract = $this->upgradeClass->prodRootDir;

            $res = $this->upgradeClass->getZipAction()->extract($filepath, $destExtract);
            if (!$res) {
                $this->upgradeClass->next = 'error';
                $this->logger->error($this->upgradeClass->getTranslator()->trans(
                    'Unable to extract file %filename% into directory %directoryname% .',
                    array(
                        '%filename%' => $filepath,
                        '%directoryname%' => $destExtract,
                    ),
                    'Modules.Autoupgrade.Admin'
                ));
                return false;
            }

            if (!empty($toRemoveOnly)) {
                foreach ($toRemoveOnly as $fileToRemove) {
                    @unlink($this->upgradeClass->prodRootDir . $fileToRemove);
                }
            }

            $this->upgradeClass->next = 'restoreDb';
            $this->logger->debug($this->upgradeClass->getTranslator()->trans('Files restored.', array(), 'Modules.Autoupgrade.Admin'));
            $this->logger->info($this->upgradeClass->getTranslator()->trans('Files restored. Now restoring database...', array(), 'Modules.Autoupgrade.Admin'));
            return true;
        }
    }
}