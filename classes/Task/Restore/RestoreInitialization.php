<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Task\Restore;

use Exception;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Backup\BackupFinder;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

/**
 * First step executed during a rollback.
 */
class RestoreInitialization extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_RESTORE;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        $this->container->getFileStorage()->cleanAllRestoreFiles();
        $restoreConfiguration = $this->container->getRestoreConfiguration();
        $state = $this->container->getRestoreState();
        $state->initDefault($restoreConfiguration);

        $state->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        // 1st, need to analyse what was wrong.
        $restoreName = $state->getRestoreName();
        $state->setRestoreFilesFilename($restoreName);
        $restoreDbFilenames = $state->getRestoreDbFilenames();

        if (empty($restoreName)) {
            $this->next = TaskName::TASK_RESTORE_EMPTY;

            return ExitCode::SUCCESS;
        }

        $files = scandir($this->container->getProperty(UpgradeContainer::BACKUP_PATH));
        // find backup filenames, and be sure they exists
        foreach ($files as $file) {
            if (preg_match('#' . preg_quote(BackupFinder::BACKUP_ZIP_NAME_PREFIX . $restoreName) . '#', $file)) {
                $state->setRestoreFilesFilename($file);
                break;
            }
        }
        if (!is_file($this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $state->getRestoreFilesFilename())) {
            $this->next = TaskName::TASK_ERROR;
            $this->setErrorFlag();
            $this->logger->error($this->translator->trans('File %s is missing: unable to restore files. Operation aborted.', [$state->getRestoreFilesFilename()]));

            return ExitCode::FAIL;
        }
        $files = scandir($this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $restoreName);
        foreach ($files as $file) {
            if (preg_match('#' . BackupFinder::BACKUP_DB_FOLDER_NAME_PREFIX . '[0-9]{6}_' . preg_quote($restoreName) . '#', $file)) {
                $restoreDbFilenames[] = $file;
            }
        }

        // order files is important !
        sort($restoreDbFilenames);
        $state->setRestoreDbFilenames($restoreDbFilenames);
        if (count($restoreDbFilenames) == 0) {
            $this->next = TaskName::TASK_ERROR;
            $this->setErrorFlag();
            $this->logger->error($this->translator->trans('No backup database files found: it would be impossible to restore the database. Operation aborted.'));

            return ExitCode::FAIL;
        }

        $this->next = TaskName::TASK_RESTORE_FILES;
        $this->logger->info($this->translator->trans('Restoring files ...'));
        // remove tmp files related to restoreFiles
        if ($this->container->getFileSystem()->exists($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::FILES_FROM_ARCHIVE_LIST)) {
            $this->container->getFileSystem()->remove($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::FILES_FROM_ARCHIVE_LIST);
        }
        if ($this->container->getFileSystem()->exists($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::FILES_TO_REMOVE_LIST)) {
            $this->container->getFileSystem()->remove($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::FILES_TO_REMOVE_LIST);
        }

        $this->container->getAnalytics()->track('Restore Launched', Analytics::WITH_RESTORE_PROPERTIES);

        return ExitCode::SUCCESS;
    }
}
