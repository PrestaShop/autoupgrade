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

namespace PrestaShop\Module\AutoUpgrade\Progress;

use InvalidArgumentException;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupComplete;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupFiles;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupInitialization;
use PrestaShop\Module\AutoUpgrade\Task\Rollback\Restore;
use PrestaShop\Module\AutoUpgrade\Task\Rollback\RestoreComplete;
use PrestaShop\Module\AutoUpgrade\Task\Rollback\RestoreDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Rollback\RestoreFiles;
use PrestaShop\Module\AutoUpgrade\Task\Update\CleanDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Update\Download;
use PrestaShop\Module\AutoUpgrade\Task\Update\Unzip;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateComplete;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateFiles;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateInitialization;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateModules;

class CompletionCalculator
{
    public function __construct()
    {
    }

    /**
     * The key baseWithoutBackup exists while the backup and upgrade are on the same workflow
     *
     * @return array<string, int>
     */
    private static function getPercentages(): array
    {
        return [
            // Backup
            BackupInitialization::class => 0,
            BackupFiles::class => 33,
            BackupDatabase::class => 66,
            BackupComplete::class => 100,

            // Update
            UpdateInitialization::class => 0,
            Download::class => 10,
            Unzip::class => 20,
            UpdateFiles::class => 40,
            UpdateDatabase::class => 60,
            UpdateModules::class => 80,
            CleanDatabase::class => 100,
            UpdateComplete::class => 100,

            // Restore
            Restore::class => 0,
            RestoreFiles::class => 33,
            RestoreDatabase::class => 66,
            RestoreComplete::class => 100,
        ];
    }

    /**
     * @return int<0, 100>
     *
     * @throws InvalidArgumentException
     */
    public function getBasePercentageOfTask(string $taskName): int
    {
        $percentages = self::getPercentages();
        if (!isset($percentages[$taskName])) {
            throw new InvalidArgumentException($taskName . ' has no percentage. Make sure to send an upgrade, backup or restore task.');
        }

        return $percentages[$taskName];
    }

    /**
     * @return int<0, 100>
     */
    public function computePercentage(Backlog $backlog, string $currentTaskClass, string $nextTaskClass): int
    {
        $currentBaseProgress = $this->getBasePercentageOfTask($currentTaskClass);
        $nextBaseProgress = $this->getBasePercentageOfTask($nextTaskClass);

        // Avoid division by zero with empty backlogs
        if (!$backlog->getInitialTotal()) {
            return $currentBaseProgress + ($nextBaseProgress - $currentBaseProgress);
        }

        // Casting as integer is equivalent to using floor(), and we want to round down.
        return (int) ($currentBaseProgress + (($nextBaseProgress - $currentBaseProgress) * ($backlog->getInitialTotal() - $backlog->getRemainingTotal()) / $backlog->getInitialTotal()));
    }
}
