<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Task\Runner;

use Exception;
use PrestaShop\Module\AutoUpgrade\AjaxResponse;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;

/**
 * Execute the whole upgrade process in a single request.
 */
class AllUpdateTasks extends ChainedTasks
{
    const initialTask = TaskName::TASK_UPDATE_INITIALIZATION;
    const TASKS_WITH_RESTART = [TaskName::TASK_UPDATE_FILES, TaskName::TASK_UPDATE_DATABASE, TaskName::TASK_UPDATE_MODULES];

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
     */
    public function setOptions(array $options): void
    {
        if (!empty($options['action'])) {
            $this->step = $options['action'];
        }
    }

    /**
     * For some steps, we may require a new request to be made.
     * For instance, in case of obsolete autoloader or loaded classes after a file copy.
     */
    protected function checkIfRestartRequested(AjaxResponse $response): bool
    {
        if (parent::checkIfRestartRequested($response)) {
            return true;
        }

        if (!$response->getStepDone()) {
            return false;
        }

        if (!in_array($response->getNext(), self::TASKS_WITH_RESTART)) {
            return false;
        }

        $this->logger->info('Restart requested. Please run the following command to continue your update:');
        $args = $_SERVER['argv'];
        foreach ($args as $key => $arg) {
            if (strpos($arg, '--action') === 0 || strpos($arg, '--config-file-path') === 0) {
                unset($args[$key]);
            }
        }
        $this->logger->info('$ ' . implode(' ', $args) . ' --action=' . $response->getNext());

        return true;
    }

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
}
