<?php

use PrestaShop\Module\AutoUpgrade\Services\UpdateNotificationService;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use Symfony\Component\HttpFoundation\JsonResponse;
use PrestaShop\Module\AutoUpgrade\Hooks\DisplayBackOfficeHeader;

class AdminAutoupgradeAjaxController extends ModuleAdminController
{
    /** @var bool */
    private $isActualPHPVersionCompatible = true;

    public function __construct()
    {
        parent::__construct();
        require_once _PS_ROOT_DIR_ . '/modules/autoupgrade/classes/VersionUtils.php';

        if (!\PrestaShop\Module\AutoUpgrade\VersionUtils::isActualPHPVersionCompatible()) {
            $this->isActualPHPVersionCompatible = false;

            return;
        }

        $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }
    }

    public function postProcess()
    {
        if (!$this->isActualPHPVersionCompatible) {
            return;
        }

        $action = Tools::getValue('action');
        $currentEmployeeId = \Context::getContext()->employee->id;

        $updateNotificationService = new UpdateNotificationService();
        $updateNotificationConfiguration = $updateNotificationService->getUpdateNotificationConfiguration();
        $updateNotificationConfiguration->addEmployee($currentEmployeeId, time() + DisplayBackOfficeHeader::INTERVAL_CHECK_TIME_IN_SECONDS);

        $updateNotificationService->saveUpdateNotificationConfiguration($updateNotificationConfiguration);

        if ($action === 'submit-update') {
            (new JsonResponse([
                'url_to_redirect' => $this->context->link->getAdminLink('AdminSelfUpgrade', true, [], ['route' => Routes::UPDATE_PAGE_VERSION_CHOICE]),
            ]))->send();
            exit;
        }
    }
}
