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

use PrestaShop\Module\AutoUpgrade\Task\Runner\SingleTask;
use PrestaShop\Module\AutoUpgrade\Tools14;

/**
 * This file is the entrypoint for all ajax requests during a upgrade, rollback or configuration.
 * In order to get the admin context, this file is copied to the admin/autoupgrade folder of your shop when the module configuration is reached.
 *
 * Calling it from the module/autoupgrade folder will have unwanted consequences on the upgrade and your shop.
 */
require_once realpath(dirname(__FILE__) . '/../../modules/autoupgrade') . '/ajax-upgradetabconfig.php';
$container = autoupgrade_init_container(dirname(__FILE__));

(new \PrestaShop\Module\AutoUpgrade\ErrorHandler($container->getLogger()))->enable();

if (!$container->getCookie()->check($_COOKIE)) {
    // If this is an XSS attempt, then we should only display a simple, secure page
    if (ob_get_level() && ob_get_length() > 0) {
        ob_clean();
    }
    echo '{wrong token}';
    http_response_code(401);
    exit(1);
}

$controller = new SingleTask($container);
$controller->setOptions(['action' => Tools14::getValue('action')]);
$controller->run();
echo $controller->getJsonResponse();
