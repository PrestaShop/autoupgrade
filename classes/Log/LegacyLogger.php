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

namespace PrestaShop\Module\AutoUpgrade\Log;

/**
 * This class reimplement the old properties of the class AdminSelfUpgrade,
 * where all the mesages were stored
 */
class LegacyLogger extends Logger
{
    protected $normalMessages = array();
    protected $severeMessages = array();
    protected $lastInfo = '';

    /**
     * @var Resource File descriptor of the log file
     */
    protected $fd = null;

    public function __constuct($fileName = null)
    {
        if (!is_null($fileName)) {
            $this->fd = fopen($fileName, 'a');
        }
    }

    public function __destruct()
    {
        if (!is_null($this->fd)) {
            fclose($this->fd);
        }
    }

    /**
     * Equivalent of the old $nextErrors
     * Used during upgrade. Will be displayed in the top right panel (not visible at the beginning)
     *
     * @var array Details of error which occured during the request. Verbose levels: ERROR
     */
    public function getErrors()
    {
        return $this->severeMessages;
    }
    
    /**
     * Equivalent of the old $nextQuickInfo
     * Used during upgrade. Will be displayed in the lower panel.
     *
     * @var array Details on what happened during the execution. Verbose levels: DEBUG / INFO / WARNING
     */
    public function getInfos()
    {
        return $this->normalMessages;
    }

    /**
     * Return the last message stored with the INFO level.
     * Equivalent of the old $next_desc
     * Used during upgrade. Will be displayed on the top left panel
     *
     * @var String Stores the main information about the current step
     */
    public function getLastInfo()
    {
        return $this->lastInfo;
    }
    
    /**
     * {@inherit}
     */
    public function log($level, $message, array $context = array())
    {
        // Specific case for INFO
        if ($level === self::INFO) {
            // If last info is already defined, move it to the messages list
            if (!empty($this->lastInfo)) {
                $this->normalMessages[] = $this->lastInfo;
            }
            $this->lastInfo = $message;
            return;
        }
        
        if ($level < self::ERROR) {
            if (!is_null($this->fd)) {
                fwrite($this->fd, $message);
            }
            $this->normalMessages[] = $message;
        } else {
            $this->severeMessages[] = $message;
        }
    }
}