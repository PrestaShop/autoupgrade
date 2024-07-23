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

use PrestaShop\Module\AutoUpgrade\Log\LegacyLogger;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use Throwable;

/**
 * In order to improve the debug of the module in case of case, we need to display the missed errors
 * directly on the user interface. This will allow a merchant to know what happened, without having to open
 * his PHP logs.
 */
class ErrorHandler
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Enable error handlers for critical steps.
     * Display hidden errors by PHP config to improve debug process.
     */
    public function enable(): void
    {
        error_reporting(E_ALL);
        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
        register_shutdown_function([$this, 'fatalHandler']);
    }

    /**
     * Function retrieving uncaught exceptions.
     */
    public function exceptionHandler(Throwable $e): void
    {
        $message = get_class($e) . ': ' . $e->getMessage();
        $this->report($e->getFile(), $e->getLine(), Logger::CRITICAL, $message, $e->getTraceAsString(), true);
        $this->terminate(64);
    }

    /**
     * Function called by PHP errors, forwarding content to the ajax response.
     */
    public function errorHandler(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting, so let it fall
            // through to the standard PHP error handler
            return false;
        }

        switch ($errno) {
            case E_USER_ERROR:
                return false; // Will be taken by fatalHandler
            case E_USER_WARNING:
            case E_WARNING:
                $type = Logger::WARNING;
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $type = Logger::NOTICE;
                break;
            default:
                $type = Logger::DEBUG;
                break;
        }

        $this->report($errfile, $errline, $type, $errstr);

        return true;
    }

    /**
     * Fatal error from PHP are not taken by the error_handler. We must check if an error occured
     * during the script shutdown.
     */
    public function fatalHandler(): void
    {
        $lastError = error_get_last();
        if ($lastError && in_array($lastError['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
            // @phpstan-ignore isset.offset (Need to check if xdebug still defines this key)
            $trace = isset($lastError['backtrace']) ? var_export($lastError['backtrace'], true) : null;
            $this->report($lastError['file'], $lastError['line'], Logger::CRITICAL, $lastError['message'], $trace, true);
        }
    }

    /**
     * Create a json encoded.
     */
    public function generateJsonLog(string $log): string
    {
        return json_encode([
            'nextQuickInfo' => $this->logger->getInfos(),
            'nextErrors' => array_merge($this->logger->getErrors(), [$log]),
            'error' => true,
            'next' => 'error',
        ]);
    }

    /**
     * Forwards message to the main class of the upgrade.
     */
    protected function report(string $file, int $line, int $type, string $message, string $trace = null, bool $display = false): void
    {
        if ($type >= Logger::CRITICAL) {
            http_response_code(500);
        }
        $log = "[INTERNAL] $file line $line - $message";
        if (!empty($trace)) {
            $log .= PHP_EOL . $trace;
        }
        $jsonResponse = $this->generateJsonLog($log);

        try {
            $this->logger->log($type, $log);
            if ($display && $this->logger instanceof LegacyLogger) {
                echo $jsonResponse;
            }
        } catch (\Exception $e) {
            echo $jsonResponse;

            $fd = fopen('php://stderr', 'w');
            fwrite($fd, $log);
            fclose($fd);
        }
    }

    /**
     * @return never
     */
    public function terminate(int $code)
    {
        exit($code);
    }
}
