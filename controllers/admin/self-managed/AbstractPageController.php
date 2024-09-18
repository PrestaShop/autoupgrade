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

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractPageController extends AbstractGlobalController
{
    const CURRENT_PAGE = '';

    protected function getPsVersion(): string
    {
        return $this->upgradeContainer->getProperty($this->upgradeContainer::PS_VERSION);
    }

    private function getPsVersionClass(): string
    {
        $psVersion = $this->getPsVersion();
        $psClass = '';

        if (version_compare($psVersion, '1.7.8.0', '<')) {
            $psClass = 'v1-7-3-0';
        } elseif (version_compare($psVersion, '9.0.0', '<')) {
            $psClass = 'v1-7-8-0';
        }

        return $psClass;
    }

    public function renderPage(string $page, array $params): string
    {
        return $this->twig->render(
            '@ModuleAutoUpgrade/layouts/layout.html.twig',
            array_merge(
                [
                    'page' => $page,
                    'ps_version' => $this->getPsVersionClass(),
                ],
                $params
            )
        );
    }

    /**
     * @param Request $request
     *
     * @return string
     *
     * @throws \Exception
     */
    public function index(Request $request): string
    {
        return $this->renderPage(
            $this::CURRENT_PAGE,
            $this->getParams($request)
        );
    }

    /**
     * @return array
     */
    abstract protected function getParams(Request $request): array;
}
