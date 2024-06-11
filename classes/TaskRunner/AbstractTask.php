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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner;

use PrestaShop\Module\AutoUpgrade\AjaxResponse;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractTask
{
    /* usage :  key = the step you want to skip
     *               value = the next step you want instead
     *	example : public static $skipAction = array();
     */
    public static $skipAction = [];

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var TranslatorInterface
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
    protected $nextParams = [];
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
            $this->logger->info($this->translator->trans('Action %s skipped', [$currentAction]));
        }
    }

    public function init()
    {
        $this->container->initPrestaShopCore();
    }

    abstract public function run();
}
