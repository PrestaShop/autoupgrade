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

namespace PrestaShop\Module\AutoUpgrade;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;

/**
 * Class creating the content to return at an ajax call.
 */
class AjaxResponse
{
    private $error;

    private $stepDone;

    private $next;

    private $next_desc;

    private $nextParams;

    private $nextQuickInfo;

    private $nextErrors;

    private $nextResponseType = 'json';

    /**
     * @var UpgradeConfiguration
     */
    private $upgradeConfiguration;

    /**
     * @var State
     */
    private $state;

    public function __construct($translator, State $state)
    {
        $this->translator = $translator;
        $this->state = $state;
    }

    /**
     * @return array of data to ready to be returned to caller
     */
    public function getResponse()
    {
        $return = array(
            'error' => $this->error,
            'stepDone' => $this->stepDone,
            'next' => $this->next,
            'status' => $this->getStatus(),
            'next_desc' => $this->next_desc,
            'nextQuickInfo' => $this->nextQuickInfo,
            'nextErrors' => $this->nextErrors,
            'nextParams' => array_merge(
                $this->nextParams,
                $this->state->export(),
                array(
                    'typeResult' => $this->nextResponseType,
                    'config' => $this->upgradeConfiguration->toArray(),
                )
            ),
        );

        if (!isset($return['nextParams']['dbStep'])) {
            $return['nextParams']['dbStep'] = 0;
        }

        return $return;
    }

    /**
     * @return string Json encoded response from $this->getResponse()
     */
    public function getJsonResponse()
    {
        return json_encode($this->getResponse());
    }

    /*
     * GETTERS
     */

    public function getError()
    {
        return $this->error;
    }

    public function getStepDone()
    {
        return $this->stepDone;
    }

    public function getNext()
    {
        return $this->next;
    }

    public function getStatus()
    {
        return ($this->getNext() == 'error' ? 'error' : 'ok');
    }

    public function getNextDesc()
    {
        return $this->next_desc;
    }

    public function getNextParams()
    {
        return $this->nextParams;
    }

    public function getNextQuickInfo()
    {
        return $this->nextQuickInfo;
    }

    public function getNextErrors()
    {
        return $this->nextErrors;
    }

    public function getUpgradeConfiguration()
    {
        return $this->upgradeConfiguration;
    }

    /*
     * SETTERS
     */

    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    public function setStepDone($stepDone)
    {
        $this->stepDone = $stepDone;
        return $this;
    }

    public function setNext($next)
    {
        $this->next = $next;
        return $this;
    }

    public function setNextDesc($next_desc)
    {
        $this->next_desc = $next_desc;
        return $this;
    }

    public function setNextParams($nextParams)
    {
        $this->nextParams = $nextParams;
        return $this;
    }

    public function setNextQuickInfo($nextQuickInfo)
    {
        $this->nextQuickInfo = $nextQuickInfo;
        return $this;
    }

    public function setNextErrors($nextErrors)
    {
        $this->nextErrors = $nextErrors;
        return $this;
    }

    public function setUpgradeConfiguration(UpgradeConfiguration $upgradeConfiguration)
    {
        $this->upgradeConfiguration = $upgradeConfiguration;
        return $this;
    }
}