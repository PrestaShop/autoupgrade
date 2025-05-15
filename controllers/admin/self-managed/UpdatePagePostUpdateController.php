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

use Context;
use PrestaShop\Module\AutoUpgrade\AjaxResponseBuilder;
use PrestaShop\Module\AutoUpgrade\DocumentationLinks;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\Stepper;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\UpdateSteps;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\VersionUtils;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdatePagePostUpdateController extends AbstractPageWithStepController
{
    const CURRENT_STEP = UpdateSteps::STEP_POST_UPDATE;

    protected function getPageTemplate(): string
    {
        return 'update';
    }

    protected function getStepTemplate(): string
    {
        return self::CURRENT_STEP;
    }

    protected function displayRouteInUrl(): ?string
    {
        return Routes::UPDATE_PAGE_POST_UPDATE;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $updateSteps = new Stepper($this->upgradeContainer->getTranslator(), TaskType::TASK_TYPE_UPDATE);
        $currentPsVersion = $this->upgradeContainer->getProperty(UpgradeContainer::PS_VERSION);
        $currentMajorVersion = VersionUtils::splitPrestaShopVersion($currentPsVersion)['major'];

        return array_merge(
            $updateSteps->getStepParams($this::CURRENT_STEP),
            [
                'exit_link' => $this->upgradeContainer->getUrlGenerator()->getShopAdminAbsolutePathFromRequest($this->request),
                'dev_doc_link' => DocumentationLinks::getDevDocUpdateAssistantPostUpdateUrl($currentMajorVersion),
                'download_logs' => $this->upgradeContainer->getLogsService()->getDownloadLogsData(TaskType::TASK_TYPE_UPDATE),
                'ps_version' => $this->upgradeContainer->getCurrentPrestaShopVersion(),
                'form_route_to_confirm_module_manager_dialog' => Routes::UPDATE_STEP_POST_UPDATE_CONFIRM_MODULE_MANAGER_DIALOG,
            ]
        );
    }

    public function confirmModuleManagerDialog(): JsonResponse
    {
        $this->upgradeContainer->initPrestaShopCore();

        // All the different versions of PrestaShop require a different controller name to the Module Manager.
        // The existing names in the database plus the management of redirect requires us to use different values, because:
        // - With AdminModulesSf on PS 1.7, we get the proper module/manage, then AdminLogin without the next parameter,
        // - With AdminModules on PS 9, we get the AdminLogin login page with the next paramater, but then a missing controller error.
        $destinationController = version_compare($this->upgradeContainer->getProperty(UpgradeContainer::PS_VERSION), '9', '>=')
            ? 'AdminModulesSf' : 'AdminModules';
        $moduleManagerLink = Context::getContext()->link->getAdminLink($destinationController);

        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::DIALOG_PARENT_ID,
            $this->getTwig()->render(
                '@ModuleAutoUpgrade/dialogs/dialog-confirm-module-manager.html.twig',
                [
                    'module_manager_link' => $moduleManagerLink,
                ]
            )
        );
    }
}
