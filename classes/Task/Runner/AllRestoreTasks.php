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
