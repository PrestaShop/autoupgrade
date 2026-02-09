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

    // MISC
    const TASK_UPDATE_CONFIG = 'UpdateConfig';
}
