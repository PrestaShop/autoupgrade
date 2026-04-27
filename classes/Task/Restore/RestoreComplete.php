<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Task\Restore;

use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;

/**
 * Only displays the success message.
 */
class RestoreComplete extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_RESTORE;

    public function run(): int
    {
        $this->container->getRestoreState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $this->logger->info($this->translator->trans('Restoration process done. Congratulations! You can now reactivate your store.'));
        $this->next = TaskName::TASK_COMPLETE;

        $this->container->getFileStorage()->cleanAllRestoreFiles();
        $this->container->getAnalytics()->track('Restore Succeeded', Analytics::WITH_RESTORE_PROPERTIES);

        $this->container->getFileStorage()->clean(UpgradeFileNames::RESTORE_CONFIG_FILENAME);

        $this->logger->info($this->translator->trans('Running opcache_reset'));
        $this->container->resetOpcache();

        $this->container->getCacheCleaner()->cleanFolders();

        return ExitCode::SUCCESS;
    }
}
