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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class BackupFiles extends AbstractTask
{
    public function run()
    {
        if (!$this->container->getUpgradeConfiguration()->get('PS_AUTOUP_BACKUP')) {
            $this->stepDone = true;
            $this->next = 'backupDb';
            $this->logger->info('File backup skipped.');

            return true;
        }

        $this->stepDone = false;
        $backupFilesFilename = $this->container->getState()->getBackupFilesFilename();
        if (empty($backupFilesFilename)) {
            $this->next = 'error';
            $this->error = true;
            $this->logger->info($this->translator->trans('Error during backupFiles', [], 'Modules.Autoupgrade.Admin'));
            $this->logger->error($this->translator->trans('[ERROR] backupFiles filename has not been set', [], 'Modules.Autoupgrade.Admin'));

            return false;
        }

        if (!$this->container->getFileConfigurationStorage()->exists(UpgradeFileNames::FILES_TO_BACKUP_LIST)) {
            /** @todo : only add files and dir listed in "originalPrestashopVersion" list */
            $filesToBackup = $this->container->getFilesystemAdapter()->listFilesInDir($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH), 'backup', false);
            $filesToBackup = array_reverse($filesToBackup);
            $this->container->getFileConfigurationStorage()->save($filesToBackup, UpgradeFileNames::FILES_TO_BACKUP_LIST);
            if (count($filesToBackup)) {
                $this->logger->debug($this->translator->trans('%s Files to backup.', [count($filesToBackup)], 'Modules.Autoupgrade.Admin'));
            }

            // delete old backup, create new
            if (!empty($backupFilesFilename) && file_exists($this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $backupFilesFilename)) {
                unlink($this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $backupFilesFilename);
            }

            $this->logger->debug($this->translator->trans('Backup files initialized in %s', [$backupFilesFilename], 'Modules.Autoupgrade.Admin'));
        }
        $filesToBackup = $this->container->getFileConfigurationStorage()->load(UpgradeFileNames::FILES_TO_BACKUP_LIST);

        $this->next = 'backupFiles';
        if (is_array($filesToBackup) && count($filesToBackup)) {
            $this->logger->info($this->translator->trans('Backup files in progress. %d files left', [count($filesToBackup)], 'Modules.Autoupgrade.Admin'));

            $this->stepDone = false;
            $res = $this->container->getZipAction()->compress($filesToBackup, $this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $backupFilesFilename);
            if (!$res) {
                $this->next = 'error';
                $this->logger->info($this->translator->trans('Unable to open archive', [], 'Modules.Autoupgrade.Admin'));

                return false;
            }
            $this->container->getFileConfigurationStorage()->save($filesToBackup, UpgradeFileNames::FILES_TO_BACKUP_LIST);
        }

        if (count($filesToBackup) <= 0) {
            $this->stepDone = true;
            $this->status = 'ok';
            $this->next = 'backupDb';
            $this->logger->debug($this->translator->trans('All files have been added to archive.', [], 'Modules.Autoupgrade.Admin'));
            $this->logger->info($this->translator->trans('All files saved. Now backing up database', [], 'Modules.Autoupgrade.Admin'));
        }
    }
}
