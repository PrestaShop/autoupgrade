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

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractGlobalController
{
    /** @var UpgradeContainer */
    protected $upgradeContainer;

    /** @var Request */
    protected $request;

    public function __construct(UpgradeContainer $upgradeContainer, Request $request)
    {
        $this->upgradeContainer = $upgradeContainer;
        $this->request = $request;
    }

    protected function getTwig()
    {
        return $this->upgradeContainer->getTwig();
    }

    protected function redirectTo(string $destinationRoute): RedirectResponse
    {
        $params = [];
        parse_str($this->request->server->get('QUERY_STRING'), $params);
        $nextQueryParams = http_build_query(array_merge($params, ['route' => $destinationRoute]));

        return new RedirectResponse($this->request->getSchemeAndHttpHost() . $this->request->getBaseUrl() . $this->request->getPathInfo() . '?' . $nextQueryParams);
    }
}
