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

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;

class BackupFiles extends AbstractTask
{
    public function run()
    {
        if (!$this->container->getUpgradeConfiguration()->get('PS_AUTOUP_BACKUP')) {
            $this->upgradeClass->stepDone = true;
            $this->upgradeClass->next = 'backupDb';
            $this->logger->info('File backup skipped.');
            return true;
        }

        $this->upgradeClass->nextParams = $this->upgradeClass->currentParams;
        $this->upgradeClass->stepDone = false;
        $backupFilesFilename = $this->container->getState()-> getBackupFilesFilename();
        if (empty($backupFilesFilename)) {
            $this->upgradeClass->next = 'error';
            $this->upgradeClass->error = 1;
            $this->logger->info($this->translator->trans('Error during backupFiles', array(), 'Modules.Autoupgrade.Admin'));
            $this->logger->error($this->translator->trans('[ERROR] backupFiles filename has not been set', array(), 'Modules.Autoupgrade.Admin'));
            return false;
        }

        if (!$this->container->getFileConfigurationStorage()->exists(UpgradeFileNames::toBackupFileList)) {
            // @todo : only add files and dir listed in "originalPrestashopVersion" list
            $filesToBackup = $this->container->getFilesystemAdapter()->listFilesInDir($this->upgradeClass->prodRootDir, 'backup', false);
            $this->container->getFileConfigurationStorage()->save($filesToBackup, UpgradeFileNames::toBackupFileList);
            if (count($filesToBackup)) {
                $this->logger->debug($this->translator->trans('%s Files to backup.', array(count($filesToBackup)), 'Modules.Autoupgrade.Admin'));
            }
            $this->upgradeClass->nextParams['filesForBackup'] = UpgradeFileNames::toBackupFileList;

            // delete old backup, create new
            if (!empty($backupFilesFilename) && file_exists($this->upgradeClass->backupPath.DIRECTORY_SEPARATOR.$backupFilesFilename)) {
                unlink($this->upgradeClass->backupPath.DIRECTORY_SEPARATOR.$backupFilesFilename);
            }

            $this->logger->debug($this->translator->trans('Backup files initialized in %s', array($backupFilesFilename), 'Modules.Autoupgrade.Admin'));
        }
        $filesToBackup = $this->container->getFileConfigurationStorage()->load(UpgradeFileNames::toBackupFileList);

        $this->upgradeClass->next = 'backupFiles';
        if (is_array($filesToBackup) && count($filesToBackup)) {
            $this->logger->info($this->translator->trans('Backup files in progress. %d files left', array(count($filesToBackup)), 'Modules.Autoupgrade.Admin'));

            $this->upgradeClass->stepDone = false;
            $res = $this->container->getZipAction()->compress($filesToBackup, $this->upgradeClass->backupPath.DIRECTORY_SEPARATOR.$backupFilesFilename);
            if (!$res) {
                $this->upgradeClass->next = 'error';
                $this->logger->info($this->translator->trans('Unable to open archive', array(), 'Modules.Autoupgrade.Admin'));
                return false;
            }
            $this->container->getFileConfigurationStorage()->save($filesToBackup, UpgradeFileNames::toBackupFileList);
        }

        if (count($filesToBackup) <= 0) {
            $this->upgradeClass->stepDone = true;
            $this->upgradeClass->status = 'ok';
            $this->upgradeClass->next = 'backupDb';
            $this->logger->debug($this->translator->trans('All files have been added to archive.', array(), 'Modules.Autoupgrade.Admin'));
            $this->logger->info($this->translator->trans('All files saved. Now backing up database', array(), 'Modules.Autoupgrade.Admin'));
        }
    }
}