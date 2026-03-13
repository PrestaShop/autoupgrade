<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Task\Backup;

use Exception;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;

class BackupComplete extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_BACKUP;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        $this->container->getBackupState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $this->stepDone = true;
        $this->next = TaskName::TASK_COMPLETE;

        $this->container->getFileStorage()->cleanAllBackupFiles();
        $updateConfiguration = $this->container->getUpdateConfiguration();
        $updateConfiguration->merge([UpdateConfiguration::BACKUP_COMPLETED => true]);
        $this->container->getConfigurationStorage()->save($updateConfiguration);

        $this->container->getAnalytics()->track('Backup Succeeded', Analytics::WITH_BACKUP_PROPERTIES);

        $this->container->getFileStorage()->clean(UpgradeFileNames::BACKUP_CONFIG_FILENAME);

        $this->logger->info($this->translator->trans('Backup completed successfully.'));

        return ExitCode::SUCCESS;
    }
}
