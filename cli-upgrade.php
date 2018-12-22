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

if (PHP_SAPI !== 'cli') {
    echo 'This script must be called from CLI';
    exit(1);
}

require_once realpath(dirname(__FILE__) . '/../../modules/autoupgrade') . '/ajax-upgradetabconfig.php';
$container = autoupgrade_init_container(dirname(__FILE__));

$container->setLogger(new PrestaShop\Module\AutoUpgrade\Log\StreamedLogger());
$controller = new \PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade\AllUpgradeTasks($container);
$controller->setOptions(getopt('', array('action::', 'channel::', 'data::')));
$controller->init();
exit($controller->run());
