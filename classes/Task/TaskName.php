<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Task;

class TaskName
{
    const TASK_COMPLETE = '';
    const TASK_ERROR = 'Error';

    // BACKUP
    const TASK_BACKUP_INITIALIZATION = 'BackupInitialization';
    const TASK_BACKUP_FILES = 'BackupFiles';
    const TASK_BACKUP_DATABASE = 'BackupDatabase';
    const TASK_BACKUP_COMPLETE = 'BackupComplete';

    // RESTORE
    const TASK_RESTORE_INITIALIZATION = 'RestoreInitialization';
    const TASK_RESTORE_EMPTY = 'RestoreEmpty';
    const TASK_RESTORE_DATABASE = 'RestoreDatabase';
    const TASK_RESTORE_FILES = 'RestoreFiles';
    const TASK_RESTORE_COMPLETE = 'RestoreComplete';

    // UPDATE
    const TASK_UPDATE_INITIALIZATION = 'UpdateInitialization';
    const TASK_DOWNLOAD = 'Download';
    const TASK_UNZIP = 'Unzip';
    const TASK_DOWNLOAD_MODULES = 'DownloadModules';
    const TASK_UNINSTALL_MODULES = 'UninstallModules';
    const TASK_UPDATE_FILES = 'UpdateFiles';
    const TASK_UPDATE_DATABASE = 'UpdateDatabase';
    const TASK_UPDATE_MODULES = 'UpdateModules';
    const TASK_CLEAN_DATABASE = 'CleanDatabase';
    const TASK_UPDATE_COMPLETE = 'UpdateComplete';
}
