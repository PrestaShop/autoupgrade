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

use PrestaShop\Module\AutoUpgrade\AjaxResponseBuilder;
use PrestaShop\Module\AutoUpgrade\DocumentationLinks;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractPageController extends AbstractGlobalController
{
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

    /**
     * @param array<string, mixed> $params
     */
    public function renderPage(string $page, array $params): string
    {
        $pageSelectors = new PageSelectors();

        return $this->getTwig()->render(
            '@ModuleAutoUpgrade/layouts/layout.html.twig',
            array_merge(
                [
                    'page' => $page,
                    'ps_version' => $this->getPsVersionClass(),
                    'data_transparency_link' => DocumentationLinks::getPrestashopProjectDataTransparencyUrl(),

                    // Data for generic error page
                    'error_template_target' => PageSelectors::PAGE_PARENT_ID,
                    'exit_to_shop_admin' => $this->upgradeContainer->getUrlGenerator()->getShopAdminAbsolutePathFromRequest($this->request),
                    'exit_to_app_home' => Routes::HOME_PAGE,
                    'submit_error_report_route' => Routes::DISPLAY_ERROR_REPORT_MODAL,
                ],
                $pageSelectors::getAllSelectors(),
                $params
            )
        );
    }

    /**
     * @param array<string, mixed> $params
     */
    public function renderPageContent(string $page, array $params): string
    {
        $pageSelectors = new PageSelectors();

        return $this->getTwig()->render(
            '@ModuleAutoUpgrade/pages/' . $page . '.html.twig',
            array_merge(
                [
                    'data_transparency_link' => DocumentationLinks::getPrestashopProjectDataTransparencyUrl(),
                ],
                $pageSelectors::getAllSelectors(),
                $params
            )
        );
    }

    /**
     * @return RedirectResponse|string
     *
     * @throws \Exception
     */
    public function index()
    {
        if ($this->request->isXmlHttpRequest()) {
            return AjaxResponseBuilder::hydrationResponse(
                PageSelectors::PAGE_PARENT_ID,
                $this->renderPageContent(
                    $this->getPageTemplate(),
                    $this->getParams()
                ),
                ['newRoute' => $this->displayRouteInUrl()]
            );
        }

        return $this->renderPage(
            $this->getPageTemplate(),
            $this->getParams()
        );
    }

    /**
     * Relative path from the templates folder of the twig file
     * to load when opening or reloading the page while being on the controller.
     * Omit "pages/" and ".html.twig" from the value.
     *
     * @see index()
     */
    abstract protected function getPageTemplate(): string;

    /**
     * Provide another route to display in the address bar when this controller
     * is called from an ajax request.
     *
     * @return Routes::*|null
     */
    protected function displayRouteInUrl(): ?string
    {
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function getParams(): array;
}
