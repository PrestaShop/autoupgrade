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

/**
 * In order to improve the debug of the module in case of case, we need to display the missed errors
 * directly on the user interface. This will allow him to know what happened, without having to open
 * its PHP logs.
 */
class ErrorHandler
{
    private $logger;

    /**
     * TODO: To be replaced by the logger later
     *
     * @param \AdminSelfUpgrade $adminSelfUpgrade
     */
    public function __construct(\AdminSelfUpgrade $adminSelfUpgrade)
    {
        $this->logger = $adminSelfUpgrade;
    }

    /**
     * Enable error handlers for critical steps.
     * Display hidden errors by PHP config to improve debug process
     */
    public function enable()
    {
        set_error_handler(array($this, 'errorHandler'));
        set_exception_handler(array($this, 'exceptionHandler'));
        register_shutdown_function(array($this, "fatalHandler"));
    }

    /**
     * Function retrieving uncaught exceptions
     * 
     * @param Throwable $e
     */
    public function exceptionHandler($e)
    {
        $this->report($e->getFile(), $e->getLine(), get_class($e), $e->getMessage());
    }

    /**
     * Function called by PHP errors, forwarding content to the ajax response
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @return boolean
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_USER_ERROR:
                return false; // Will be taken by fatalHandler
            case E_USER_WARNING:
            case E_WARNING:
                $type = 'WARNING';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $type = 'NOTICE';
                break;
            default:
                $type = "Unknown error type ($errno)";
                break;
        }

        $this->report($errfile, $errline, $type, $errstr);
        return true;
    }

    /**
     * Fatal error from PHP are not taken by the error_handler. We must check if an error occured
     * during the script shutdown.
     */
    public function fatalHandler()
    {
        $lastError = error_get_last();
        if ($lastError && in_array($lastError['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR), true)) {
            $this->report($lastError['file'], $lastError['line'], 'CRITICAL', $lastError['message'], true);
        }
    }

    /**
     * Forwards message to the main class of the upgrade
     * 
     * @param string $file
     * @param int $line
     * @param string $type
     * @param string $message
     * @param bool $display
     */
    protected function report($file, $line, $type, $message, $display = false)
    {
        $log = "[INTERNAL] $file line $line - $type: $message";
        
        try {
            $this->logger->nextErrors[] = $log;
            if ($display) {
                $this->logger->next = 'error';
                $this->logger->error = true;
                // FixMe: In CLI, we should display something else. A new logger
                $this->logger->displayAjax();
            }
        } catch (\Exception $e) {
            echo '{"nextErrors":["'.$log.'"],"error":true}';
            
            $fd = fopen('php://stderr', 'w');
            fwrite($fd, $log);
            fclose($fd);
        }
    }
}