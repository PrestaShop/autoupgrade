<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Router;

use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @param Request $request
     *
     * @return Response|string
     */
    public function handle(Request $request)
    {
        $routeName = $request->query->get('route') ?? Routes::HOME_PAGE;
        $redirected = $request->query->get('_redirected') === '1';

        $route = $routeName = isset(RoutesConfig::ROUTES[$routeName]) ? $routeName : Routes::ERROR_404;

        if (!$redirected) {
            $route = (new MiddlewareHandler($this->upgradeContainer))->process($route);

            if ($routeName !== $route) {
                $this->dirtyRedirectToRoute($request, $route);
            }
        }

        $routeParams = RoutesConfig::ROUTES[$route];
        $method = $routeParams['method'];

        return (new $routeParams['controller']($this->upgradeContainer, $request))->$method();
    }

    /**
     * @param Request $request
     * @param Routes::* $route
     *
     * @return never
     */
    private function dirtyRedirectToRoute(Request $request, string $route): void
    {
        $newUrl = $this->upgradeContainer->getUrlGenerator()->getUrlToRoute($request, $route, ['_redirected' => '1']);

        header('Location: ' . $newUrl, true, 307);
        exit;
    }
}
