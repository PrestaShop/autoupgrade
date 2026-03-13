<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
