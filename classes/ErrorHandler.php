<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade;

use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Log\WebLogger;
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
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            case E_STRICT:
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
            // clean all php errors to got clean error handled by ourself
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            // @phpstan-ignore isset.offset (Need to check if xdebug still defines this key)
            $trace = isset($lastError['backtrace']) ? var_export($lastError['backtrace'], true) : null;
            $this->report($lastError['file'], $lastError['line'], Logger::CRITICAL, $lastError['message'], $trace, true);
        }
    }

    /**
     * Create a json encoded.
     */
    public function generateJsonLog(string $log, string $type): string
    {
        return json_encode([
            'nextQuickInfo' => array_merge($this->logger->getLogs(), [$type . ' - ' . $this->logger->cleanFromSensitiveData($log)]),
            'error' => true,
            'next' => 'error',
        ]);
    }

    /**
     * Forwards message to the main class of the upgrade.
     */
    protected function report(string $file, int $line, int $type, string $message, ?string $trace = null, bool $display = false): void
    {
        if ($type >= Logger::CRITICAL) {
            http_response_code(500);
        }
        $log = "$file line $line - $message";
        if (!empty($trace)) {
            $log .= PHP_EOL . $trace;
        }
        $jsonResponse = $this->generateJsonLog($log, Logger::$levels[$type]);

        try {
            $this->logger->log($type, $log);
            if ($display && $this->logger instanceof WebLogger) {
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
