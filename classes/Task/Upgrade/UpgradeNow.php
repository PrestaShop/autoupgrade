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

namespace PrestaShop\Module\AutoUpgrade\Task\Upgrade;

use Exception;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Upgrader;

/**
 * very first step of the upgrade process. The only thing done is the selection
 * of the next step.
 */
class UpgradeNow extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_UPDATE;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        $this->logger->info($this->translator->trans('Starting upgrade...'));
        $this->container->getState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $upgrader = $this->container->getUpgrader();

        if ($upgrader->isLastVersion()) {
            $this->next = '';
            $this->logger->info($this->translator->trans('Your shop is currently running the latest compatible version. No updates are needed at this time.'));

            return ExitCode::SUCCESS;
        }

        $this->logger->info($this->translator->trans('Destination version: %s', [$upgrader->getDestinationVersion()]));

        switch ($upgrader->getChannel()) {
            case Upgrader::CHANNEL_LOCAL:
                $this->next = 'unzip';
                $this->logger->debug($this->translator->trans('Downloading step has been skipped, upgrade process will now unzip the local archive.'));
                $this->logger->info($this->translator->trans('Shop deactivated. Extracting files...'));
                break;
            default:
                $this->next = 'download';
                $this->logger->info($this->translator->trans('Shop deactivated. Now downloading... (this can take a while)'));
                $this->logger->debug($this->translator->trans('Downloaded archive will come from %s', [$upgrader->getOnlineDestinationRelease()->getZipDownloadUrl()]));
                $this->logger->debug($this->translator->trans('MD5 hash will be checked against %s', [$upgrader->getOnlineDestinationRelease()->getZipMd5()]));
        }
        $this->container->getAnalytics()->track('Upgrade Launched', Analytics::WITH_UPDATE_PROPERTIES);

        return ExitCode::SUCCESS;
    }
}
