<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

use Symfony\Component\HttpFoundation\Request;

/**
 * @param string $callerFilePath Path to the caller file. Needed as the two files are not in the same folder
 *
 * @return void
 */
function autoupgrade_require_autoload($callerFilePath)
{
    // the following test confirm the directory exists
    if (empty($_POST['dir'])) {
        echo 'No admin directory provided (dir). Update assistant cannot proceed.';
        exit(1);
    }

    // defines.inc.php can not exists (1.3.0.1 for example)
    // but we need _PS_ROOT_DIR_
    if (!defined('_PS_ROOT_DIR_')) {
        define('_PS_ROOT_DIR_', realpath($callerFilePath . '/../../'));
    }

    if (!defined('_PS_MODULE_DIR_')) {
        define('_PS_MODULE_DIR_', _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR);
    }

    define('AUTOUPGRADE_MODULE_DIR', _PS_MODULE_DIR_ . 'autoupgrade' . DIRECTORY_SEPARATOR);
    require_once AUTOUPGRADE_MODULE_DIR . 'vendor/autoload.php';
}

/**
 * Set constants & general values used by the autoupgrade.
 *
 * @param Request $request
 *
 * @return \PrestaShop\Module\AutoUpgrade\UpgradeContainer
 */
function autoupgrade_init_container($request)
{
    $dir = $request->get('dir');
    define('_PS_ADMIN_DIR_', _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . $dir);

    if (_PS_ADMIN_DIR_ !== realpath(_PS_ADMIN_DIR_)) {
        echo 'wrong directory: ' . $dir;
        exit(1);
    }

    $container = new \PrestaShop\Module\AutoUpgrade\UpgradeContainer(_PS_ROOT_DIR_, _PS_ADMIN_DIR_);
    $container->getBackupState()->importFromArray(empty($_REQUEST['params']) ? [] : $_REQUEST['params']);
    $container->getRestoreState()->importFromArray(empty($_REQUEST['params']) ? [] : $_REQUEST['params']);
    $container->getUpdateState()->importFromArray(empty($_REQUEST['params']) ? [] : $_REQUEST['params']);

    return $container;
}
