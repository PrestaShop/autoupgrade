<?php

namespace PrestaShop\Module\AutoUpgrade\Router;

use PrestaShop\Module\AutoUpgrade\Controller\HomePageController;
use PrestaShop\Module\AutoUpgrade\Controller\UpdateVersionChoicePageController;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\HttpFoundation\Request;
use RuntimeException;

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

    const ROUTES = [
        'home' => [
            'controller' => HomePageController::class,
            'method' => 'index',
        ],
        'update-version-choice' => [
            'controller' => UpdateVersionChoicePageController::class,
            'method' => 'index',
        ],
        'update-version-choice-step' => [
            'ajax-only' => true,
            'controller' => UpdateVersionChoicePageController::class,
            'method' => 'step',
        ]
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
        $route = self::ROUTES[$routeKey] ?? self::ROUTES['home'];

        $method = $route['method'];
        if (!isset($method)) {
            throw new RuntimeException('Invalid method for route.');
        }

        if (!isset($route['ajax-only']) || ($route['ajax-only'] && $request->isXmlHttpRequest())) {
            return (new $route['controller']($this->upgradeContainer))->$method($request);
        }

        if (isset($route['fallback'])) {
            return $this->processRoute($route['fallback'], $request);
        }

        throw new RuntimeException('This route is only accessible via AJAX requests.');
    }
}
