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

namespace PrestaShop\Module\AutoUpgrade\Log;

/**
 * This class retrieves all message to display during the upgrade / rollback tasks.
 */
abstract class Logger implements LoggerInterface
{
    const DEBUG = 1;
    const INFO = 2;
    const NOTICE = 3;
    const WARNING = 4;
    const ERROR = 5;
    const CRITICAL = 6;
    const ALERT = 7;
    const EMERGENCY = 8;

    protected static $levels = [
        self::DEBUG => 'DEBUG',
        self::INFO => 'INFO',
        self::NOTICE => 'NOTICE',
        self::WARNING => 'WARNING',
        self::ERROR => 'ERROR',
        self::CRITICAL => 'CRITICAL',
        self::ALERT => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    ];

    public function alert($message, array $context = [])
    {
        $this->log(self::ALERT, $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->log(self::DEBUG, $message, $context);
    }

    public function emergency($message, array $context = [])
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->log(self::ERROR, $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->log(self::INFO, $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->log(self::NOTICE, $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Equivalent of the old $nextErrors
     * Used during upgrade. Will be displayed in the top right panel (not visible at the beginning).
     *
     * @return array Details of error which occured during the request. Verbose levels: ERROR
     */
    public function getErrors()
    {
        return [];
    }

    /**
     * Equivalent of the old $nextQuickInfo
     * Used during upgrade. Will be displayed in the lower panel.
     *
     * @return array Details on what happened during the execution. Verbose levels: DEBUG / INFO / WARNING
     */
    public function getInfos()
    {
        return [];
    }

    /**
     * Return the last message stored with the INFO level.
     * Equivalent of the old $next_desc
     * Used during upgrade. Will be displayed on the top left panel.
     *
     * @return string Stores the main information about the current step
     */
    public function getLastInfo()
    {
        return '';
    }
}
