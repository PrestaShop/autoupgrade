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

namespace PrestaShop\Module\AutoUpgrade\Task\Runner;

use Exception;
use PrestaShop\Module\AutoUpgrade\AjaxResponse;
use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\TaskRepository;
use Throwable;

/**
 * Execute the whole process in a single request, useful in CLI.
 */
abstract class ChainedTasks extends AbstractTask
{
    /**
     * @var string
     */
    protected $step;

    /**
     * @var string
     */
    protected $stepClass;

    /**
     * Execute all the tasks from a specific initial step, until the end (complete or error).
     *
     * @return int Return code (0 for success, any value otherwise)
     *
     * @throws Exception
     */
    public function run(): int
    {
        $this->setupLogging();

        $requireRestart = false;
        while ($this->canContinue() && !$requireRestart) {
            $controller = TaskRepository::get($this->step, $this->container);
            $this->stepClass = get_class($controller);
            $controller->init();
            $this->disableOPCacheIfNecessary();
            $this->logger->debug('Step ' . $this->step);
            try {
                $controller->run();
            } catch (Throwable $t) {
                $controller->setErrorFlag();
                throw $t;
            }

            $result = $controller->getResponse();
            $requireRestart = $this->checkIfRestartRequested($result);
            $this->error = $result->getError();
            $this->stepDone = $result->getStepDone();
            $this->step = $this->next = $result->getNext();
            $this->nextParams = $result->getNextParams();
        }

        return (int) ($this->error || $this->step === TaskName::TASK_ERROR);
    }

    /**
     * Customize the execution context with several options.
     *
     * @param array<string, string> $options
     */
    abstract public function setOptions(array $options): void;

    /**
     * Tell the while loop if it can continue.
     */
    protected function canContinue(): bool
    {
        if ($this->error || $this->next === TaskName::TASK_COMPLETE) {
            return false;
        }

        return $this->step !== TaskName::TASK_ERROR;
    }

    /**
     * For some steps, we may require a new request to be made.
     * Return true for stopping the process.
     */
    protected function checkIfRestartRequested(AjaxResponse $response): bool
    {
        return false;
    }

    /**
     * @throws Exception
     */
    private function setupLogging(): void
    {
        $logsState = $this->container->getLogsState();
        $initializationSteps = [TaskName::TASK_BACKUP_INITIALIZATION, TaskName::TASK_UPDATE_INITIALIZATION, TaskName::TASK_RESTORE_INITIALIZATION];

        if (in_array($this->step, $initializationSteps)) {
            if (php_sapi_name() !== 'cli') {
                $timeZone = $this->getCoreTimezone();
                $logsState->setTimeZone($timeZone);
                date_default_timezone_set($timeZone);
            }
            $timestamp = date('Y-m-d-His');
            switch ($this->step) {
                case TaskName::TASK_BACKUP_INITIALIZATION:
                    $logsState->setActiveBackupLogFromDateTime($timestamp);
                    break;
                case TaskName::TASK_RESTORE_INITIALIZATION:
                    $logsState->setActiveRestoreLogFromDateTime($timestamp);
                    break;
                case TaskName::TASK_UPDATE_INITIALIZATION:
                    $logsState->setActiveUpdateLogFromDateTime($timestamp);
                    break;
            }
        } else {
            $timeZone = $logsState->getTimeZone();
            if ($timeZone) {
                date_default_timezone_set($timeZone);
            }
        }
    }

    private function getCoreTimezone(): string
    {
        if ($this->step === TaskName::TASK_RESTORE_INITIALIZATION) {
            $timeZone = date_default_timezone_get();
        } else {
            try {
                $this->container->initPrestaShopCore();
                $timeZone = DbWrapper::getValue('SELECT `value` FROM `' . _DB_PREFIX_ . 'configuration` WHERE `name` = \'PS_TIMEZONE\'');
            } catch (Throwable $t) {
                $timeZone = date_default_timezone_get();
            }
        }

        return $timeZone;
    }

    private function disableOPCacheIfNecessary(): void
    {
        $disableSteps = [
            TaskName::TASK_UPDATE_FILES,
            TaskName::TASK_UPDATE_DATABASE,
            TaskName::TASK_UPDATE_MODULES,
            TaskName::TASK_RESTORE_FILES,
            TaskName::TASK_RESTORE_DATABASE,
        ];

        $logSteps = [
            TaskName::TASK_UPDATE_INITIALIZATION,
            TaskName::TASK_RESTORE_INITIALIZATION,
        ];

        $allRelevantSteps = array_merge($disableSteps, $logSteps);

        if (!in_array($this->step, $allRelevantSteps, true)) {
            return;
        }

        if (!(bool) @ini_get('opcache.enable')) {
            return;
        }

        $revalidateFrequency = (int) @ini_get('opcache.revalidate_freq');
        $validateTimestamps = (int) @ini_get('opcache.validate_timestamps');

        $needsDisabling = ($revalidateFrequency > 0 || $validateTimestamps === 0);
        if (!$needsDisabling) {
            return;
        }

        if (in_array($this->step, $logSteps, true)) {
            $this->logger->debug($this->container->getTranslator()->trans('OPCache configuration will be changed for the duration of the process.'));
        } else {
            $this->container->resetOpcache();
            @ini_set('opcache.revalidate_freq', '0');
            @ini_set('opcache.validate_timestamps', '1');
        }
    }
}
