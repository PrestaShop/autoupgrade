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

use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminAutoupgradeAjaxController extends ModuleAdminController
{
    /** @var Autoupgrade */
    public $module;

    /** @var bool */
    private $isActualPHPVersionCompatible = true;

    /**
     * @var UpgradeContainer
     */
    private $upgradeContainer;

    public function __construct()
    {
        parent::__construct();

        if (!$this->module->initAutoloaderIfCompliant()) {
            $this->isActualPHPVersionCompatible = false;

            return;
        }

        $this->upgradeContainer = $this->module->getUpgradeContainer();
    }

    public function postProcess()
    {
        if (!$this->isActualPHPVersionCompatible) {
            return false;
        }

        $action = Tools::getValue('action');
        $timeValue = Tools::getValue('value');
        $currentEmployeeId = \Context::getContext()->employee->id;

        $updateNotificationService = $this->upgradeContainer->getUpdateNotificationService();
        $updateNotificationConfiguration = $updateNotificationService->getUpdateNotificationConfiguration();

        $updateNotificationConfiguration->addEmployeeReminderChoice($currentEmployeeId, $timeValue);

        $updateNotificationService->saveUpdateNotificationConfiguration($updateNotificationConfiguration);

        if ($action === 'submit-update') {
            (new JsonResponse([
                'url_to_redirect' => $this->context->link->getAdminLink('AdminSelfUpgrade', true, [], ['route' => Routes::UPDATE_PAGE_VERSION_CHOICE]),
            ]))->send();
            exit;
        }

        return true;
    }
}
