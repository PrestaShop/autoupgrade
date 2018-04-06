<?php
/*
 * 2007-2018 PrestaShop
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * 
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\TaskRunner;

use PrestaShop\Module\AutoUpgrade\AjaxResponse;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\TaskRepository;

/**
 * Execute the whole upgrade process in a single request.
 */
class AllUpgradeTasks extends AbstractTask
{
    const initialTask = 'upgradeNow';

    private $step = self::initialTask;

    public function run()
    {
        $requireRestart = false;
        while ($this->canContinue() && !$requireRestart) {
            echo "\n=== Step ".$this->step."\n";
            $controller = TaskRepository::get($this->step, $this->container);
            $controller->run();

            $result = $controller->getResponse();
            $requireRestart = $this->checkIfRestartRequested($result);
            $this->error = $result->getError();
            $this->stepDone = $result->getStepDone();
            $this->step = $result->getNext();
        }

        return (int) ($this->error || $this->next === 'error');
    }

    /**
     * Customize the execution context with several options
     * > action: Replace the initial step to run
     * > channel: Makes a specific upgrade (minor, major etc.)
     * > data: Loads an encoded array of data coming from another request
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        if (!empty($options['action'])) {
            $this->step = $options['action'];
        }

        if (!empty($options['channel'])) {
            $this->container->getUpgradeConfiguration()->merge(array(
                'channel' => $options['channel'],
            ));
            $this->container->getUpgrader()->channel = $options['channel'];
            $this->container->getUpgrader()->checkPSVersion(true);
        }

        if (!empty($options['data'])) {
            $this->container->getState()->importFromEncodedData($options['data']);
        }
    }


    /**
     * Tell the while loop if it can continue
     *
     * @return boolean
     */
    protected function canContinue()
    {
        if (empty($this->step)) {
            return false;
        }

        if ($this->error) {
            return false;
        }

        return ! in_array($this->step, array('error'));
    }

    /**
     * For some steps, we may require a new request to be made.
     * For instance, in case of obsolete autoloader or loaded classes after a file copy.
     */
    protected function checkIfRestartRequested(AjaxResponse $response)
    {
        if (!$response->getStepDone()) {
            return false;
        }

        if (!in_array($this->step, array('upgradeFiles'))) {
            return false;
        }

        $this->logger->info('Restart requested. Please run the following command to continue your upgrade:');
        $args = $_SERVER['argv'];
        foreach ($args as $key => $arg) {
            if (strpos($arg, '--data') === 0) {
                unset($args[$key]);
            }
        }
        $this->logger->info("$ ".implode(' ', $args). ' --action='. $response->getNext(). ' --data='. $this->getEncodedResponse());

        return true;
    }

    /**
     * Set default config
     */
    public function init()
    {
        parent::init();
        $this->container->getState()->initDefault(
                $this->container->getUpgrader(),
                $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH),
                $this->container->getProperty(UpgradeContainer::PS_VERSION));
    }
}