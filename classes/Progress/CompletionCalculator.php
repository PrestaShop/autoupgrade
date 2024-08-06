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
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Task\Rollback\RestoreDb;
use PrestaShop\Module\AutoUpgrade\Task\Rollback\RestoreFiles;
use PrestaShop\Module\AutoUpgrade\Task\Rollback\Rollback;
use PrestaShop\Module\AutoUpgrade\Task\Rollback\RollbackComplete;
use PrestaShop\Module\AutoUpgrade\Task\Upgrade\BackupDb;
use PrestaShop\Module\AutoUpgrade\Task\Upgrade\BackupFiles;
use PrestaShop\Module\AutoUpgrade\Task\Upgrade\CleanDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Upgrade\Download;
use PrestaShop\Module\AutoUpgrade\Task\Upgrade\Unzip;
use PrestaShop\Module\AutoUpgrade\Task\Upgrade\UpgradeComplete;
use PrestaShop\Module\AutoUpgrade\Task\Upgrade\UpgradeDb;
use PrestaShop\Module\AutoUpgrade\Task\Upgrade\UpgradeFiles;
use PrestaShop\Module\AutoUpgrade\Task\Upgrade\UpgradeModules;
use PrestaShop\Module\AutoUpgrade\Task\Upgrade\UpgradeNow;

class CompletionCalculator
{
    /** @var UpgradeConfiguration */
    private $upgradeConfiguration;

    public function __construct(UpgradeConfiguration $upgradeConfiguration)
    {
        $this->upgradeConfiguration = $upgradeConfiguration;
    }

    /**
     * The key baseWithoutBackup exists while the backup and upgrade are on the same workflow
     *
     * @return array<string, array{base:int, baseWithoutBackup:int|null}>
     */
    private static function getPercentages(): array
    {
        return [
            // Upgrade (+ backup)
            UpgradeNow::class => ['base' => 0],
            Download::class => ['base' => 5, 'baseWithoutBackup' => 10],
            Unzip::class => ['base' => 10, 'baseWithoutBackup' => 20],
            BackupFiles::class => ['base' => 20],
            BackupDb::class => ['base' => 40],
            UpgradeFiles::class => ['base' => 50, 'baseWithoutBackup' => 40],
            UpgradeDb::class => ['base' => 70, 'baseWithoutBackup' => 60],
            UpgradeModules::class => ['base' => 90, 'baseWithoutBackup' => 80],
            CleanDatabase::class => ['base' => 100],
            UpgradeComplete::class => ['base' => 100],

            // Restore
            Rollback::class => ['base' => 0],
            RestoreFiles::class => ['base' => 33],
            RestoreDb::class => ['base' => 66],
            RollbackComplete::class => ['base' => 100],
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

        $withoutBackup = !$this->upgradeConfiguration->shouldBackupFilesAndDatabase();

        if ($withoutBackup && isset($percentages[$taskName]['baseWithoutBackup'])) {
            return $percentages[$taskName]['baseWithoutBackup'];
        }

        return $percentages[$taskName]['base'];
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
