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

namespace PrestaShop\Module\AutoUpgrade\Task\Update;

use Exception;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

/**
 * very first step of the upgrade process. The only thing done is the selection
 * of the next step.
 */
class UpdateInitialization extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_UPDATE;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        $this->logger->info($this->translator->trans('Starting update...'));
        $this->container->getFileStorage()->cleanAllUpdateFiles();

        $this->container->getUpdateState()->initDefault(
            $this->container->getProperty(UpgradeContainer::PS_VERSION),
            $this->container->getUpgrader(),
            $this->container->getUpdateConfiguration()
        );
        $this->container->getUpdateState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $upgrader = $this->container->getUpgrader();
        if ($upgrader->isLastVersion()) {
            $this->next = TaskName::TASK_COMPLETE;
            $this->logger->info($this->translator->trans('Your store is currently running the latest compatible version. No updates are needed at this time.'));

            return ExitCode::SUCCESS;
        }

        $this->logger->info($this->translator->trans('Destination version: %s', [$this->container->getUpdateState()->getDestinationVersion()]));

        switch ($this->container->getUpdateConfiguration()->getChannelOrDefault()) {
            case UpgradeConfiguration::CHANNEL_LOCAL:
                $this->next = TaskName::TASK_UNZIP;
                $this->logger->debug($this->translator->trans('Downloading step has been skipped, update process will now unzip the local archive.'));
                $this->logger->info($this->translator->trans('Store deactivated. Extracting files...'));
                break;
            default:
                $this->next = TaskName::TASK_DOWNLOAD;
                $this->logger->info($this->translator->trans('Store deactivated. Now downloading... (this can take a while)'));
                $this->logger->debug($this->translator->trans('Downloaded archive will come from %s', [$upgrader->getOnlineDestinationRelease()->getZipDownloadUrl()]));
                $this->logger->debug($this->translator->trans('MD5 hash will be checked against %s', [$upgrader->getOnlineDestinationRelease()->getZipMd5()]));
        }
        $this->container->getAnalytics()->track('Upgrade Launched', Analytics::WITH_UPDATE_PROPERTIES);

        return ExitCode::SUCCESS;
    }
}
