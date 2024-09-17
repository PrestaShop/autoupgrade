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

namespace PrestaShop\Module\AutoUpgrade\Router;

use PrestaShop\Module\AutoUpgrade\Controller\HomePageController;
use PrestaShop\Module\AutoUpgrade\Controller\UpdatePageBackupController;
use PrestaShop\Module\AutoUpgrade\Controller\UpdatePagePostUpdateController;
use PrestaShop\Module\AutoUpgrade\Controller\UpdatePageUpdateController;
use PrestaShop\Module\AutoUpgrade\Controller\UpdatePageUpdateOptionsController;
use PrestaShop\Module\AutoUpgrade\Controller\UpdatePageVersionChoiceController;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class Router
{
    /**
     * @var UpgradeContainer
     */
    protected $upgradeContainer;

    public function __construct(UpgradeContainer $upgradeContainer)
    {
        $this->upgradeContainer = $upgradeContainer;
    }

    const HOME_PAGE = 'home-page';
    const UPDATE_PAGE_VERSION_CHOICE = 'update-page-version-choice';
    const UPDATE_STEP_VERSION_CHOICE = 'update-step-version-choice';
    const UPDATE_PAGE_UPDATE_OPTIONS = 'update-page-update-options';
    const UPDATE_STEP_UPDATE_OPTIONS = 'update-step-update-options';
    const UPDATE_PAGE_BACKUP = 'update-page-backup';
    const UPDATE_STEP_BACKUP = 'update-step-backup';
    const UPDATE_PAGE_UPDATE = 'update-page-update';
    const UPDATE_STEP_UPDATE = 'update-step-update';
    const UPDATE_PAGE_POST_UPDATE = 'update-page-post-update';
    const UPDATE_STEP_POST_UPDATE = 'update-step-post-update';

    const ROUTES = [
        self::HOME_PAGE => [
            'controller' => HomePageController::class,
            'method' => 'index',
        ],
        self::UPDATE_PAGE_VERSION_CHOICE => [
            'controller' => UpdatePageVersionChoiceController::class,
            'method' => 'index',
        ],
        self::UPDATE_STEP_VERSION_CHOICE => [
            'ajax-only' => true,
            'controller' => UpdatePageVersionChoiceController::class,
            'method' => 'step',
            'fallback' => self::UPDATE_PAGE_VERSION_CHOICE,
        ],
        self::UPDATE_PAGE_UPDATE_OPTIONS => [
            'controller' => UpdatePageUpdateOptionsController::class,
            'method' => 'index',
        ],
        self::UPDATE_STEP_UPDATE_OPTIONS => [
            'ajax-only' => true,
            'controller' => UpdatePageUpdateOptionsController::class,
            'method' => 'step',
            'fallback' => self::UPDATE_PAGE_UPDATE_OPTIONS,
        ],
        self::UPDATE_PAGE_BACKUP => [
            'controller' => UpdatePageBackupController::class,
            'method' => 'index',
        ],
        self::UPDATE_STEP_BACKUP => [
            'ajax-only' => true,
            'controller' => UpdatePageBackupController::class,
            'method' => 'step',
            'fallback' => self::UPDATE_PAGE_BACKUP,
        ],
        self::UPDATE_PAGE_UPDATE => [
            'controller' => UpdatePageUpdateController::class,
            'method' => 'index',
        ],
        self::UPDATE_STEP_UPDATE => [
            'ajax-only' => true,
            'controller' => UpdatePageUpdateController::class,
            'method' => 'step',
            'fallback' => self::UPDATE_PAGE_UPDATE,
        ],
        self::UPDATE_PAGE_POST_UPDATE => [
            'controller' => UpdatePagePostUpdateController::class,
            'method' => 'index',
        ],
        self::UPDATE_STEP_POST_UPDATE => [
            'ajax-only' => true,
            'controller' => UpdatePagePostUpdateController::class,
            'method' => 'step',
            'fallback' => self::UPDATE_PAGE_POST_UPDATE,
        ],
    ];

    /**
     * @param Request $request
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function handle(Request $request): string
    {
        $route = $request->query->get('route');

        if (!isset(self::ROUTES[$route])) {
            $route = self::HOME_PAGE;
        }

        return $this->processRoute($route, $request);
    }

    /**
     * @param string $routeKey
     * @param Request $request
     *
     * @return string
     *
     * @throws RuntimeException
     */
    private function processRoute(string $routeKey, Request $request): string
    {
        $route = self::ROUTES[$routeKey];

        $method = $route['method'];

        if (!isset($route['ajax-only']) || ($route['ajax-only'] === true && $request->isXmlHttpRequest())) {
            return (new $route['controller']($this->upgradeContainer))->$method($request);
        }

        return $this->processRoute($route['fallback'], $request);
    }
}
