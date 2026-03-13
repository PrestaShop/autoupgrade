<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Router;

use Symfony\Component\HttpFoundation\Request;

class UrlGenerator
{
    /** @var string */
    private $adminFolder;

    public function __construct(string $adminFolder)
    {
        $this->adminFolder = $adminFolder;
    }

    public function getShopAbsolutePathFromRequest(Request $request): string
    {
        $path = explode('/', $request->getBasePath());
        $keyOfAdminInPath = array_search($this->adminFolder, $path);

        array_splice($path, $keyOfAdminInPath);

        return implode('/', $path) ?: '/';
    }

    public function getShopAdminAbsolutePathFromRequest(Request $request): string
    {
        return rtrim($this->getShopAbsolutePathFromRequest($request), '/') . '/' . $this->adminFolder;
    }

    /**
     * @param Request $request
     * @param string $destinationRoute
     * @param array<string, mixed> $params
     *
     * @return string
     */
    public function getUrlToRoute(Request $request, string $destinationRoute, array $params = []): string
    {
        $queryStringParams = [];
        parse_str($request->server->get('QUERY_STRING'), $queryStringParams);
        $nextQueryParams = http_build_query(array_merge($queryStringParams, $params, ['route' => $destinationRoute]));

        return $request->getSchemeAndHttpHost() . $request->getBaseUrl() . rtrim($request->getPathInfo(), '/') . '?' . $nextQueryParams;
    }
}
