<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Task\Runner;

use Exception;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;

/**
 * Execute the whole upgrade process in a single request.
 */
class AllBackupTasks extends ChainedTasks
{
    const initialTask = TaskName::TASK_BACKUP_INITIALIZATION;

    /**
     * @var string
     */
    protected $step = self::initialTask;

    /**
     * Set default config on first run.
     *
     * @throws Exception
     */
    public function init(): void
    {
        if ($this->step === self::initialTask) {
            parent::init();
        }
    }

    public function setOptions(array $options): void
    {
    }
}
