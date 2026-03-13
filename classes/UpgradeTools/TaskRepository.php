<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use Exception;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupComplete;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupFiles;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupInitialization;
use PrestaShop\Module\AutoUpgrade\Task\NullTask;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreComplete;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreEmpty;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreFiles;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreInitialization;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\Update\CleanDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Update\Download;
use PrestaShop\Module\AutoUpgrade\Task\Update\DownloadModules;
use PrestaShop\Module\AutoUpgrade\Task\Update\UninstallModules;
use PrestaShop\Module\AutoUpgrade\Task\Update\Unzip;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateComplete;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateFiles;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateInitialization;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateModules;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class TaskRepository
{
    /**
     * @throws Exception
     */
    public static function get(string $step, UpgradeContainer $container): AbstractTask
    {
        switch ($step) {
            // RESTORE
            case TaskName::TASK_RESTORE_INITIALIZATION:
                return new RestoreInitialization($container);
            case TaskName::TASK_RESTORE_EMPTY:
                return new RestoreEmpty($container);
            case TaskName::TASK_RESTORE_DATABASE:
                return new RestoreDatabase($container);
            case TaskName::TASK_RESTORE_FILES:
                return new RestoreFiles($container);
            case TaskName::TASK_RESTORE_COMPLETE:
                return new RestoreComplete($container);

            // BACKUP
            case TaskName::TASK_BACKUP_INITIALIZATION:
                return new BackupInitialization($container);
            case TaskName::TASK_BACKUP_DATABASE:
                return new BackupDatabase($container);
            case TaskName::TASK_BACKUP_FILES:
                return new BackupFiles($container);
            case TaskName::TASK_BACKUP_COMPLETE:
                return new BackupComplete($container);

            // UPGRADE
            case TaskName::TASK_UPDATE_INITIALIZATION:
                return new UpdateInitialization($container);
            case TaskName::TASK_DOWNLOAD:
                return new Download($container);
            case TaskName::TASK_UNZIP:
                return new Unzip($container);
            case TaskName::TASK_DOWNLOAD_MODULES:
                return new DownloadModules($container);
            case TaskName::TASK_UNINSTALL_MODULES:
                return new UninstallModules($container);
            case TaskName::TASK_UPDATE_FILES:
                return new UpdateFiles($container);
            case TaskName::TASK_UPDATE_DATABASE:
                return new UpdateDatabase($container);
            case TaskName::TASK_UPDATE_MODULES:
                return new UpdateModules($container);
            case TaskName::TASK_CLEAN_DATABASE:
                return new CleanDatabase($container);
            case TaskName::TASK_UPDATE_COMPLETE:
                return new UpdateComplete($container);
        }
        error_log('Unknown step ' . $step);

        return new NullTask($container);
    }
}
