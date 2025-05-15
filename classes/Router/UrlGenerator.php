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
