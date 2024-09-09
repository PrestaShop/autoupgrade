<?php

namespace PrestaShop\Module\AutoUpgrade\Router;

use PrestaShop\Module\AutoUpgrade\Controller\WelcomeController;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\HttpFoundation\Request;

class Router
{
    protected $upgradeContainer;

    public function __construct(UpgradeContainer $upgradeContainer)
    {
        $this->upgradeContainer = $upgradeContainer;
    }

    const ROUTES = [
        'welcome' => [
            'controller' => WelcomeController::class,
            'method' => 'index',
            'params' => [],
        ],
    ];

    public function handle(Request $request): string
    {
        $route = $request->query->get('route');

        if (empty(self::ROUTES[$route])) {
            throw new \InvalidArgumentException('Oh no! The route does not exist');
        }
        $route = self::ROUTES[$route];
        $method = $route['method'];

        return (new $route['controller']($this->upgradeContainer))->$method($request);
    }
}
