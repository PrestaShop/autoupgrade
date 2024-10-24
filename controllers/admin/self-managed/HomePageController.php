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

use PrestaShop\Module\AutoUpgrade\AjaxResponseBuilder;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use Symfony\Component\HttpFoundation\JsonResponse;

class HomePageController extends AbstractPageController
{
    const CURRENT_PAGE = 'home';
    const CURRENT_ROUTE = Routes::HOME_PAGE;
    const FORM_FIELDS = [
        'route_choice' => 'route_choice',
    ];
    const FORM_OPTIONS = [
        'update_value' => 'update',
        'restore_value' => 'restore',
    ];

    public function submit(): JsonResponse
    {
        $routeChoice = $this->request->request->get(self::FORM_FIELDS['route_choice']);

        if ($routeChoice === self::FORM_OPTIONS['update_value']) {
            return AjaxResponseBuilder::nextRouteResponse(Routes::UPDATE_PAGE_VERSION_CHOICE);
        }

        // if is not update is restore
        if ($this->getParams()['empty_backup']) {
            return AjaxResponseBuilder::errorResponse('You can\'t access this route because you have no backups.', 401);
        }

        return AjaxResponseBuilder::nextRouteResponse(Routes::RESTORE_PAGE_BACKUP_SELECTION);
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $backupFinder = $this->upgradeContainer->getBackupFinder();

        return [
            'empty_backup' => empty($backupFinder->getAvailableBackups()),
            'form_route_to_submit' => Routes::HOME_PAGE_SUBMIT_FORM,
            'form_fields' => self::FORM_FIELDS,
            'form_options' => self::FORM_OPTIONS,
        ];
    }
}
