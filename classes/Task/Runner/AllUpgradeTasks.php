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
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

/**
 * Execute the whole upgrade process in a single request.
 */
class AllUpgradeTasks extends ChainedTasks
{
    const initialTask = 'upgradeNow';
    const TASKS_WITH_RESTART = ['upgradeFiles', 'upgradeDb', 'upgradeModules'];

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
     * @throws Exception
     */
    public function setOptions(array $options): void
    {
        if (!empty($options['action'])) {
            $this->step = $options['action'];
        }

        if (!empty($options['channel'])) {
            $this->container->getUpgradeConfiguration()->merge([
                'channel' => $options['channel'],
                // Switch on default theme if major upgrade (i.e: 1.6 -> 1.7)
                'PS_AUTOUP_CHANGE_DEFAULT_THEME' => ($options['channel'] === 'major'),
            ]);
            $this->container->getUpgrader()->channel = $options['channel'];
            $this->container->getUpgrader()->checkPSVersion(true);
        }

        if (!empty($options['data'])) {
            $this->container->getState()->importFromEncodedData($options['data']);
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

        $this->logger->info('Restart requested. Please run the following command to continue your upgrade:');
        $args = $_SERVER['argv'];
        foreach ($args as $key => $arg) {
            if (strpos($arg, '--data') === 0 || strpos($arg, '--action') === 0) {
                unset($args[$key]);
            }
        }
        $this->logger->info('$ ' . implode(' ', $args) . ' --action=' . $response->getNext() . ' --data=' . $this->getEncodedResponse());

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
            $this->container->getState()->initDefault(
                $this->container->getUpgrader(),
                $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH),
                $this->container->getProperty(UpgradeContainer::PS_VERSION));
        }
    }
}
