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

namespace PrestaShop\Module\AutoUpgrade;

use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;

/**
 * Class creating the content to return at an ajax call.
 */
class AjaxResponse
{
    /**
     * Used during upgrade.
     *
     * @var bool Supposed to store a boolean in case of error
     */
    private $error = false;

    /**
     * Used during upgrade.
     *
     * @var bool Inform when the step is completed
     */
    private $stepDone = true;

    /**
     * Used during upgrade. "N/A" as value otherwise.
     *
     * @var string Next step to call (can be the same as the previous one)
     */
    private $next = 'N/A';

    /**
     * @var array<string, string|array<string>> Params to send (upgrade conf, details on the work to do ...)
     */
    private $nextParams = [];

    /**
     * Request format of the data to return.
     * Seems to be never modified. Converted as const.
     */
    const RESPONSE_FORMAT = 'json';

    /**
     * @var UpgradeConfiguration
     */
    private $upgradeConfiguration;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var State
     */
    private $state;

    public function __construct(State $state, Logger $logger)
    {
        $this->state = $state;
        $this->logger = $logger;
    }

    /**
     * @return array<string, mixed> of data to ready to be returned to caller
     */
    public function getResponse(): array
    {
        return [
            'error' => $this->error,
            'stepDone' => $this->stepDone,
            'next' => $this->next,
            'status' => $this->getStatus(),
            'next_desc' => $this->logger->getLastInfo(),
            'nextQuickInfo' => $this->logger->getInfos(),
            'nextErrors' => $this->logger->getErrors(),
            'nextParams' => array_merge(
                $this->nextParams,
                $this->state->export(),
                [
                    'typeResult' => self::RESPONSE_FORMAT,
                    'config' => $this->upgradeConfiguration->toArray(),
                ]
            ),
        ];
    }

    /**
     * @return string Json encoded response from $this->getResponse()
     */
    public function getJson(): string
    {
        return json_encode($this->getResponse());
    }

    // GETTERS

    public function getError(): ?bool
    {
        return $this->error;
    }

    public function getStepDone(): ?bool
    {
        return $this->stepDone;
    }

    public function getNext(): ?string
    {
        return $this->next;
    }

    public function getStatus(): string
    {
        return $this->getNext() == 'error' ? 'error' : 'ok';
    }

    /**
     * @return array<string, string|array<string>>
     */
    public function getNextParams(): array
    {
        return $this->nextParams;
    }

    public function getUpgradeConfiguration(): UpgradeConfiguration
    {
        return $this->upgradeConfiguration;
    }

    // SETTERS

    public function setError(?bool $error): AjaxResponse
    {
        $this->error = $error;

        return $this;
    }

    public function setStepDone(?bool $stepDone): AjaxResponse
    {
        $this->stepDone = $stepDone;

        return $this;
    }

    public function setNext(?string $next): AjaxResponse
    {
        $this->next = $next;

        return $this;
    }

    /**
     * @param array<string, string|array<string>> $nextParams
     */
    public function setNextParams(array $nextParams): AjaxResponse
    {
        $this->nextParams = $nextParams;

        return $this;
    }

    public function setUpgradeConfiguration(UpgradeConfiguration $upgradeConfiguration): AjaxResponse
    {
        $this->upgradeConfiguration = $upgradeConfiguration;

        return $this;
    }
}
