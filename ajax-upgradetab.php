<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Router\Router;
use PrestaShop\Module\AutoUpgrade\Task\Runner\SingleTask;
use Symfony\Component\HttpFoundation\Request;

/*
 * This file is the entrypoint for all ajax requests during a upgrade, rollback or configuration.
 * In order to get the admin context, this file is copied to the admin/autoupgrade folder of your shop when the module configuration is reached.
 *
 * Calling it from the module/autoupgrade folder will have unwanted consequences on the upgrade and your shop.
 */

ini_set('display_errors', '0');
ob_start();

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

    // Clear previous output to ensure the response is a valid JSON
    if (ob_get_level() && ob_get_length() > 0) {
        ob_clean();
    }

    echo $controller->getJsonResponse();
} else {
    $response = (new Router($container))->handle($request);

    // Clear previous output to ensure the response is a valid JSON
    if (ob_get_level() && ob_get_length() > 0) {
        ob_clean();
    }

    if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
        $response->send();
    } else {
        echo $response;
    }
}
