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
