<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Task\Backup;

use Exception;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class BackupInitialization extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_BACKUP;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        $this->container->getFileStorage()->cleanAllBackupFiles();
        $this->container->getBackupState()->initDefault(
            $this->container->getProperty(UpgradeContainer::PS_VERSION)
        );
        $this->container->getBackupState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $this->container->getAnalytics()->track('Backup Launched', Analytics::WITH_BACKUP_PROPERTIES);

        $this->stepDone = true;

        $this->logger->info($this->translator->trans('Starting backup...'));
        $this->next = TaskName::TASK_BACKUP_FILES;

        return ExitCode::SUCCESS;
    }
}
