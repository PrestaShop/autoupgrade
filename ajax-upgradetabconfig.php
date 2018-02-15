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

use PrestaShop\Module\AutoUpgrade\Tools14;

if (function_exists('date_default_timezone_set'))
{
	// date_default_timezone_get calls date_default_timezone_set, which can provide warning
	$timezone = @date_default_timezone_get();
	date_default_timezone_set($timezone);
}

/**
 * Set constants & general values used by the autoupgrade
 *
 * @param string $callerFilePath Path to the caller file. Needed as the two files are not in the same folder
 */
function autoupgrade_ajax_init($callerFilePath)
{
    define('_PS_ADMIN_DIR_', realpath($callerFilePath.'/../'));
    require_once(realpath($callerFilePath.'/../../config/config.inc.php'));

    if (!defined('_PS_MODULE_DIR_'))
        define('_PS_MODULE_DIR_', realpath($callerFilePath.'/../../').'/modules/');

    define('AUTOUPGRADE_MODULE_DIR', _PS_MODULE_DIR_.'autoupgrade/');
    require_once(AUTOUPGRADE_MODULE_DIR.'functions.php');

    // the following test confirm the directory exists
    if (!isset($_POST['dir']))
        die('no directory');

    // defines.inc.php can not exists (1.3.0.1 for example)
    // but we need _PS_ROOT_DIR_
    if (!defined('_PS_ROOT_DIR_'))
        define('_PS_ROOT_DIR_', realpath($callerFilePath.'/../../'));

    require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/classes/Tools14.php');
    if (!class_exists('Tools', false))
        eval('class Tools extends Tools14{}');

    $dir = Tools14::safeOutput(Tools14::getValue('dir'));

    if (realpath($callerFilePath.'/../../').DIRECTORY_SEPARATOR.$dir!== realpath(realpath($callerFilePath.'/../../').DIRECTORY_SEPARATOR.$dir))
        die('wrong directory :'.(isset($_POST['dir']) ? $dir : ''));

    if (!defined('_MYSQL_ENGINE_'))
        define('_MYSQL_ENGINE_', 'MyISAM');

    if (!defined('_PS_TOOL_DIR_'))
        define('_PS_TOOL_DIR_', _PS_ROOT_DIR_.'/tools/');

    //require(_PS_ADMIN_DIR_.'/functions.php');
    include(AUTOUPGRADE_MODULE_DIR.'AdminSelfUpgrade.php');

    $_GET['ajax'] = '1';

    // TODO: create error handler for logs, preventing the json response to be altered
    ini_set('display_errors', 'off');
}