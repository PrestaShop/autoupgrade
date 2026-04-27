<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Task\Runner;

use InvalidArgumentException;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;

/**
 * Execute the whole upgrade process in a single request.
 */
class AllRestoreTasks extends ChainedTasks
{
    const initialTask = TaskName::TASK_RESTORE_INITIALIZATION;

    /**
     * @var string
     */
    protected $step = self::initialTask;

    /**
     * Customize the execution context with several options
     * > action: Replace the initial step to run
     * > channel: Makes a specific upgrade (minor, major etc.)
     * > data: Loads an encoded array of data coming from another request.
     *
     * @param array<string, string> $options
     *
     * @throws \Exception
     */
    public function setOptions(array $options): void
    {
        $restoreConfiguration = $this->container->getRestoreConfiguration();
        $restoreConfigurationValidator = $this->container->getRestoreConfigurationValidator();

        $errors = $restoreConfigurationValidator->validate($options);

        if (!empty($errors)) {
            throw new InvalidArgumentException(reset($errors)['message']);
        }

        $restoreConfiguration->merge($options);

        $this->container->getConfigurationStorage()->save($restoreConfiguration);
    }

    /**
     * Set default config on first run.
     */
    public function init(): void
    {
        // Do nothing
    }
}
