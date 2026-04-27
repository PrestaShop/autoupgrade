<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Task\Restore;

use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;

class RestoreEmpty extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_RESTORE;

    public function run(): int
    {
        $this->logger->info($this->translator->trans('Nothing to restore'));
        $this->next = TaskName::TASK_RESTORE_COMPLETE;

        return ExitCode::SUCCESS;
    }
}
