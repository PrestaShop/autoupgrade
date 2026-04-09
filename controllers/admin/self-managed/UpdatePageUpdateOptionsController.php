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
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\Stepper;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\UpdateSteps;
use PrestaShop\Module\AutoUpgrade\Twig\ValidatorToFormFormater;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdatePageUpdateOptionsController extends AbstractPageWithStepController
{
    const CURRENT_STEP = UpdateSteps::STEP_UPDATE_OPTIONS;

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
        return Routes::UPDATE_PAGE_UPDATE_OPTIONS;
    }

    /**
     * @throws \Exception
     */
    public function saveOption(): JsonResponse
    {
        $updateConfiguration = $this->upgradeContainer->getUpdateConfiguration();

        $config = [
            UpgradeConfiguration::PS_AUTOUP_CUSTOM_MOD_DESACT => $this->request->request->getBoolean(UpgradeConfiguration::PS_AUTOUP_CUSTOM_MOD_DESACT, false),
            UpgradeConfiguration::PS_AUTOUP_UNINSTALL_NON_COMPAT_MODS => $this->request->request->getBoolean(UpgradeConfiguration::PS_AUTOUP_UNINSTALL_NON_COMPAT_MODS, false),
            UpgradeConfiguration::PS_AUTOUP_REGEN_EMAIL => $this->request->request->getBoolean(UpgradeConfiguration::PS_AUTOUP_REGEN_EMAIL, false),
            UpgradeConfiguration::PS_DISABLE_OVERRIDES => $this->request->request->getBoolean(UpgradeConfiguration::PS_DISABLE_OVERRIDES, false),
        ];

        $errors = $this->upgradeContainer->getConfigurationValidator()->validate($config);

        if (empty($errors)) {
            // One specific option requires the Core to store the value in database.
            $this->upgradeContainer->initPrestaShopCore();
            UpgradeConfiguration::updatePSDisableOverrides($config[UpgradeConfiguration::PS_DISABLE_OVERRIDES]);

            $updateConfiguration->merge($config);
            $this->upgradeContainer->getConfigurationStorage()->save($updateConfiguration);
        }

        return $this->getRefreshOfForm(array_merge(
            $this->getParams(),
            ['errors' => ValidatorToFormFormater::format($errors)]
        ));
    }

    public function submit(): JsonResponse
    {
        return AjaxResponseBuilder::nextRouteResponse(Routes::UPDATE_STEP_BACKUP_OPTIONS);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $this->upgradeContainer->initPrestaShopCore();
        $updateConfiguration = $this->upgradeContainer->getConfigurationStorage()->loadUpdateConfiguration();
        $updateSteps = new Stepper($this->upgradeContainer->getTranslator(), TaskType::TASK_TYPE_UPDATE);

        return array_merge(
            $updateSteps->getStepParams(self::CURRENT_STEP),
            [
                'form_route_to_save' => Routes::UPDATE_STEP_UPDATE_OPTIONS_SAVE_OPTION,
                'form_route_to_submit' => Routes::UPDATE_STEP_UPDATE_OPTIONS_SUBMIT_FORM,

                'form_fields' => [
                    'deactive_non_native_modules' => [
                        // Starting from v9.0.0, this field has no effect. Services are now loaded regardless of the module's state, so this setting is no longer relevant.
                        'visible' => version_compare('9.0.0', $this->upgradeContainer->getUpgrader()->getDestinationVersion(), '>'),
                        'field' => UpgradeConfiguration::PS_AUTOUP_CUSTOM_MOD_DESACT,
                        'value' => $updateConfiguration->shouldDeactivateCustomModules(),
                    ],
                    'uninstall_incompatible_modules' => [
                        'field' => UpgradeConfiguration::PS_AUTOUP_UNINSTALL_NON_COMPAT_MODS,
                        'value' => $updateConfiguration->shouldUninstallNonCompatibleModules(),
                    ],
                    'regenerate_email_templates' => [
                        'field' => UpgradeConfiguration::PS_AUTOUP_REGEN_EMAIL,
                        'value' => $updateConfiguration->shouldRegenerateMailTemplates(),
                    ],
                    'disable_all_overrides' => [
                        'field' => UpgradeConfiguration::PS_DISABLE_OVERRIDES,
                        'value' => !$updateConfiguration->isOverrideAllowed(),
                    ],
                ],
            ]
        );
    }

    /**
     * @param array<string, mixed> $params
     */
    private function getRefreshOfForm(array $params): JsonResponse
    {
        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::STEP_PARENT_ID,
            $this->getTwig()->render(
                '@ModuleAutoUpgrade/steps/' . $this->getStepTemplate() . '.html.twig',
                $params
            ),
            ['newRoute' => $this->displayRouteInUrl()]
        );
    }
}
