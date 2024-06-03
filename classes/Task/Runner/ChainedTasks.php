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

namespace PrestaShop\Module\AutoUpgrade\Task\Runner;

use Exception;
use PrestaShop\Module\AutoUpgrade\AjaxResponse;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
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
     * Execute all the tasks from a specific initial step, until the end (complete or error).
     *
     * @return int Return code (0 for success, any value otherwise)
     *
     * @throws Exception
     */
    public function run(): int
    {
        $requireRestart = false;
        while ($this->canContinue() && !$requireRestart) {
            $this->logger->info('=== Step ' . $this->step);
            $controller = TaskRepository::get($this->step, $this->container);
            $controller->init();
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

        return (int) ($this->error || $this->step === 'error');
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
        if (empty($this->step)) {
            return false;
        }

        if ($this->error) {
            return false;
        }

        return $this->step !== 'error';
    }

    /**
     * For some steps, we may require a new request to be made.
     * Return true for stopping the process.
     */
    protected function checkIfRestartRequested(AjaxResponse $response): bool
    {
        return false;
    }
}
