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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupComplete;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupFiles;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupInitialization;
use PrestaShop\Module\AutoUpgrade\Task\Miscellaneous\CheckFilesVersion;
use PrestaShop\Module\AutoUpgrade\Task\Miscellaneous\CompareReleases;
use PrestaShop\Module\AutoUpgrade\Task\Miscellaneous\UpdateConfig;
use PrestaShop\Module\AutoUpgrade\Task\NullTask;
use PrestaShop\Module\AutoUpgrade\Task\Rollback\NoRestoreFound;
use PrestaShop\Module\AutoUpgrade\Task\Rollback\Restore;
use PrestaShop\Module\AutoUpgrade\Task\Rollback\RestoreComplete;
use PrestaShop\Module\AutoUpgrade\Task\Rollback\RestoreDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Rollback\RestoreFiles;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\Update\CleanDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Update\Download;
use PrestaShop\Module\AutoUpgrade\Task\Update\Unzip;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateComplete;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateFiles;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateInitialization;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateModules;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class TaskRepository
{
    public static function get(string $step, UpgradeContainer $container): AbstractTask
    {
        switch ($step) {
            // MISCELLANEOUS (upgrade configuration, checks etc.)
            case TaskName::TASK_CHECK_FILES_VERSION:
                return new CheckFilesVersion($container);
            case TaskName::TASK_COMPARE_RELEASES:
                return new CompareReleases($container);
            case TaskName::TASK_UPDATE_CONFIG:
                return new UpdateConfig($container);

            // RESTORE
            case TaskName::TASK_RESTORE:
                return new Restore($container);
            case TaskName::TASK_NO_RESTORE_FOUND:
                return new NoRestoreFound($container);
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
            case TaskName::TASK_CLEAN_DATABASE:
                return new CleanDatabase($container);
            case TaskName::TASK_DOWNLOAD:
                return new Download($container);
            case TaskName::TASK_UPDATE_COMPLETE:
                return new UpdateComplete($container);
            case TaskName::TASK_UPDATE_DATABASE:
                return new UpdateDatabase($container);
            case TaskName::TASK_UPDATE_FILES:
                return new UpdateFiles($container);
            case TaskName::TASK_UPDATE_MODULES:
                return new UpdateModules($container);
            case TaskName::TASK_UNZIP:
                return new Unzip($container);
        }
        error_log('Unknown step ' . $step);

        return new NullTask($container);
    }
}
