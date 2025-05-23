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

namespace PrestaShop\Module\AutoUpgrade\Task\Restore;

use PrestaShop\Module\AutoUpgrade\Analytics;
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
        $this->logger->info($this->translator->trans('Restoration process done. Congratulations! You can now reactivate your store.'));
        $this->next = TaskName::TASK_COMPLETE;

        $this->container->getFileStorage()->cleanAllRestoreFiles();
        $this->container->getAnalytics()->track('Restore Succeeded', Analytics::WITH_RESTORE_PROPERTIES);

        $this->container->getRestoreState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $this->logger->info($this->translator->trans('Running opcache_reset'));
        $this->container->resetOpcache();

        $this->container->getCacheCleaner()->cleanFolders();

        return ExitCode::SUCCESS;
    }
}
