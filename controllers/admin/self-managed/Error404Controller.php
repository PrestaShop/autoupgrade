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

namespace PrestaShop\Module\AutoUpgrade\Controller;

use Symfony\Component\HttpFoundation\Response;

class Error404Controller extends AbstractPageController
{
    public function index()
    {
        $response = parent::index();

        if ($response instanceof Response) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        } else {
            http_response_code(Response::HTTP_NOT_FOUND);
        }

        return $response;
    }

    protected function getPageTemplate(): string
    {
        return 'errors/' . Response::HTTP_NOT_FOUND;
    }

    protected function getParams(): array
    {
        return [
            // TODO: assets_base_path is provided by all controllers. What about a asset() twig function instead?
            'assets_base_path' => $this->upgradeContainer->getAssetsEnvironment()->getAssetsBaseUrl($this->request),

            'error_code' => Response::HTTP_NOT_FOUND,
        ];
    }
}
