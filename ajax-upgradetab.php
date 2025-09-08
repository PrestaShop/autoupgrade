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

use PrestaShop\Module\AutoUpgrade\Router\Router;
use PrestaShop\Module\AutoUpgrade\Task\Runner\SingleTask;
use Symfony\Component\HttpFoundation\Request;

/**
 * This file is the entrypoint for all ajax requests during a upgrade, rollback or configuration.
 * In order to get the admin context, this file is copied to the admin/autoupgrade folder of your shop when the module configuration is reached.
 *
 * Calling it from the module/autoupgrade folder will have unwanted consequences on the upgrade and your shop.
 */
require_once realpath(dirname(__FILE__) . '/../../modules/autoupgrade') . '/ajax-upgradetabconfig.php';

autoupgrade_require_autoload(dirname(__FILE__));

$request = Request::createFromGlobals();

$container = autoupgrade_init_container($request);

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

$container->loadNecessaryClasses();
$action = $request->get('action');

if (!empty($action)) {
    $controller = new SingleTask($container);
    $controller->setOptions(['action' => $action]);
    $controller->run();
    echo $controller->getJsonResponse();
} else {
    $response = (new Router($container))->handle($request);
    if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
        $response->send();
    } else {
        echo $response;
    }
}
