<?php
/**
* 2007-2016 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\Module\AutoUpgrade\Tools14;

require_once(realpath(dirname(__FILE__).'/../../modules/autoupgrade').'/ajax-upgradetabconfig.php');
autoupgrade_ajax_init(dirname(__FILE__));

$adminObj = new AdminSelfUpgrade();

if (!$adminObj->checkToken()) {
    // If this is an XSS attempt, then we should only display a simple, secure page
    if (ob_get_level() && ob_get_length() > 0)
        ob_clean();
    echo '{wrong token}';
    die(1);
}

switch (Tools14::getValue('action')) {
    // MISCELLANEOUS (upgrade configuration, checks etc.)
    case 'checkFilesVersion':
        $controller = new PrestaShop\Module\AutoUpgrade\TaskRunner\Miscellaneous\CheckFilesVersion($adminObj);
        break;
    case 'compareReleases':
        $controller = new PrestaShop\Module\AutoUpgrade\TaskRunner\Miscellaneous\CompareReleases($adminObj);
        break;
    case 'getChannelInfo':
        $controller = new PrestaShop\Module\AutoUpgrade\TaskRunner\Miscellaneous\GetChannelInfo($adminObj);
        break;
    case 'updateConfig':
        $controller = new \PrestaShop\Module\AutoUpgrade\TaskRunner\Miscellaneous\UpdateConfig($adminObj);
        break;

    // ROLLBACK
    case 'noRollbackFound':
        $controller = new PrestaShop\Module\AutoUpgrade\TaskRunner\Rollback\NoRollbackFound($adminObj);
        break;
    case 'restoreDb':
        $controller = new PrestaShop\Module\AutoUpgrade\TaskRunner\Rollback\RestoreDb($adminObj);
        break;
    case 'restoreFiles':
        $controller = new PrestaShop\Module\AutoUpgrade\TaskRunner\Rollback\RestoreFiles($adminObj);
        break;
    case 'rollback':
        $controller = new \PrestaShop\Module\AutoUpgrade\TaskRunner\Rollback\Rollback($adminObj);
        break;
    case 'rollbackComplete':
        $controller = new \PrestaShop\Module\AutoUpgrade\TaskRunner\Rollback\RollbackComplete($adminObj);
        break;

    // UPGRADE
    case 'backupDb':
        $controller = new PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade\BackupDb($adminObj);
        break;
    case 'backupFiles':
        $controller = new PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade\BackupFiles($adminObj);
        break;
    case 'cleanDatabase':
        $controller = new PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade\CleanDatabase($adminObj);
        break;
    case 'download':
        $controler = new PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade\Download($adminObj);
        break;
    case 'removeSamples':
        $controller = new \PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade\RemoveSamples($adminObj);
        break;
    case 'upgradeComplete':
        $controller = new PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade\UpgradeComplete($adminObj);
        break;
    case 'upgradeDb':
        $controller = new \PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade\UpgradeDb($adminObj);
        break;
    case 'upgradeFiles':
        $controller = new \PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade\UpgradeFiles($adminObj);
        break;
    case 'upgradeModules':
        $controller = new \PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade\UpgradeModules($adminObj);
        break;
    case 'upgradeNow':
        $controller = new PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade\UpgradeNow($adminObj);
        break;
    case 'unzip':
        $controller = new PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade\Unzip($adminObj);
        break;

    default:
        error_log('Action '.Tools14::getValue('action').' is unknown!', 0);
}

if ($controller instanceof \PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask) {
    $controller->run();
}

$adminObj->displayAjax();

