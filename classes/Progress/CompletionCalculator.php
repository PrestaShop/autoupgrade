<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Progress;

use InvalidArgumentException;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupComplete;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupFiles;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupInitialization;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreComplete;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreFiles;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreInitialization;
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

class CompletionCalculator
{
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
            Download::class => 1,
            Unzip::class => 4,
            DownloadModules::class => 8,
            UninstallModules::class => 16,
            UpdateFiles::class => 24,
            UpdateDatabase::class => 60,
            UpdateModules::class => 89,
            CleanDatabase::class => 100,
            UpdateComplete::class => 100,

            // Restore
            RestoreInitialization::class => 0,
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
