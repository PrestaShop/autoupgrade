<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\AutoUpgrade\Task\Backup;

use Exception;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Progress\Backlog;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class BackupFiles extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_BACKUP;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        // The first call must init the list of files to backup.
        if (!$this->container->getFileStorage()->exists(UpgradeFileNames::FILES_TO_BACKUP_LIST)) {
            return $this->warmUp();
        }

        $state = $this->container->getBackupState();

        $this->next = TaskName::TASK_BACKUP_FILES;

        $backlog = Backlog::fromContents($this->container->getFileStorage()->load(UpgradeFileNames::FILES_TO_BACKUP_LIST));

        if (!$backlog->getRemainingTotal()) {
            $this->next = TaskName::TASK_BACKUP_DATABASE;
            $this->logger->debug($this->translator->trans('All files have been added to archive.'));
            $this->logger->info($this->translator->trans('All files saved. Now backing up database'));
            $this->stepDone = true;
        } else {
            $this->stepDone = false;
            $res = $this->container->getZipAction()->compress($backlog, $this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $state->getBackupFilesFilename());
            if (!$res) {
                $this->next = TaskName::TASK_ERROR;
                $this->logger->info($this->translator->trans('Unable to open archive'));

                return ExitCode::FAIL;
            }
            $this->container->getFileStorage()->save($backlog->dump(), UpgradeFileNames::FILES_TO_BACKUP_LIST);

            $this->logger->info($this->translator->trans('Backup files in progress. %d files left', [$backlog->getRemainingTotal()]));
            $state->setProgressPercentage(
                $this->container->getCompletionCalculator()->computePercentage($backlog, self::class, BackupDatabase::class)
            );
        }

        return ExitCode::SUCCESS;
    }

    /**
     * First call of this task needs a warmup, where we load the files list to be backup.
     *
     * @throws Exception
     */
    protected function warmUp(): int
    {
        $state = $this->container->getBackupState();
        $state->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $backupFilesFilename = $state->getBackupFilesFilename();
        if (empty($backupFilesFilename)) {
            $this->next = TaskName::TASK_ERROR;
            $this->setErrorFlag();
            $this->logger->error($this->translator->trans('Backup filename has not been set'));

            return ExitCode::FAIL;
        }

        $filesToBackup = $this->container->getFilesystemAdapter()->listFilesInDir($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH), 'backup', false);

        $totalFilesToBackup = count($filesToBackup);
        $this->container->getFileStorage()->save(
            (new Backlog($filesToBackup, $totalFilesToBackup))->dump(),
            UpgradeFileNames::FILES_TO_BACKUP_LIST
        );

        if ($totalFilesToBackup === 0) {
            $this->logger->error($this->translator->trans('Unable to find files to backup.'));
            $this->next = TaskName::TASK_ERROR;

            return ExitCode::FAIL;
        }

        $this->logger->info($this->translator->trans('%s files will be added to the backup.', [$totalFilesToBackup]));
        $this->next = TaskName::TASK_BACKUP_FILES;
        $this->stepDone = false;

        return ExitCode::SUCCESS;
    }
}
