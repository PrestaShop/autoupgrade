<?php

/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\TaskRunner;

use PrestaShop\Module\AutoUpgrade\AjaxResponse;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

abstract class AbstractTask
{
    /* usage :  key = the step you want to skip
     *               value = the next step you want instead
     *	example : public static $skipAction = array();
     */
    public static $skipAction = array();

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator
     */
    protected $translator;

    /**
     * @var UpgradeContainer
     */
    protected $container;

    // Task progress details
    protected $stepDone;
    protected $status;
    protected $error;
    protected $nextParams = array();
    protected $next;

    public function __construct(UpgradeContainer $container)
    {
        $this->container = $container;
        $this->logger = $this->container->getLogger();
        $this->translator = $this->container->getTranslator();
        $this->checkTaskMayRun();
    }

    /**
     * @return string base64 encoded data from AjaxResponse
     */
    public function getEncodedResponse()
    {
        return base64_encode($this->getJsonResponse());
    }

    /**
     * @return string Json encoded data from AjaxResponse
     */
    public function getJsonResponse()
    {
        return $this->getResponse()->getJson();
    }

    /**
     * Get result of the task and data to send to the next request.
     *
     * @return AjaxResponse
     */
    public function getResponse()
    {
        $response = new AjaxResponse($this->container->getState(), $this->logger);

        return $response->setError($this->error)
            ->setStepDone($this->stepDone)
            ->setNext($this->next)
            ->setNextParams($this->nextParams)
            ->setUpgradeConfiguration($this->container->getUpgradeConfiguration());
    }

    private function checkTaskMayRun()
    {
        // PrestaShop demo mode
        if (defined('_PS_MODE_DEMO_') && _PS_MODE_DEMO_ == true) {
            return;
        }

        $currentAction = get_class($this);
        if (isset(self::$skipAction[$currentAction])) {
            $this->next = self::$skipAction[$currentAction];
            $this->logger->info($this->translator->trans('Action %s skipped', array($currentAction), 'Modules.Autoupgrade.Admin'));
        }
    }

    public function init()
    {
        $this->container->initPrestaShopCore();
    }

    abstract public function run();
}
