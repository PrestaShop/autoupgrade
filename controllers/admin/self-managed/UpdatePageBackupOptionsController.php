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
use PrestaShop\Module\AutoUpgrade\Twig\Events;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\Stepper;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\UpdateSteps;
use PrestaShop\Module\AutoUpgrade\Twig\ValidatorToFormFormater;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdatePageBackupOptionsController extends AbstractPageWithStepController
{
    const CURRENT_STEP = UpdateSteps::STEP_BACKUP;

    protected function getPageTemplate(): string
    {
        return 'update';
    }

    protected function getStepTemplate(): string
    {
        return 'backup-options';
    }

    protected function displayRouteInUrl(): ?string
    {
        return Routes::UPDATE_PAGE_BACKUP_OPTIONS;
    }

    public function submitBackup(): JsonResponse
    {
        $imagesIncluded = $this->upgradeContainer->getUpdateConfiguration()->shouldBackupImages();

        return $this->displayDialog('dialog-backup', [
            'image_included' => $imagesIncluded,
            'dialogId' => 'dialog-confirm-backup',

            'form_route_to_confirm_backup' => Routes::UPDATE_STEP_BACKUP_CONFIRM_BACKUP,
        ]);
    }

    public function submitUpdate(): JsonResponse
    {
        return $this->displayDialog('dialog-update', [
            'backup_completed' => $this->upgradeContainer->getUpdateConfiguration()->isBackupCompleted(),
            'dialogId' => 'dialog-confirm-update',

            'form_route_to_confirm' => Routes::UPDATE_STEP_BACKUP_CONFIRM_UPDATE,

            // TODO: assets_base_path is provided by all controllers. What about a asset() twig function instead?
            'assets_base_path' => $this->upgradeContainer->getAssetsEnvironment()->getAssetsBaseUrl($this->request),
        ]);
    }

    public function startBackup(): JsonResponse
    {
        return AjaxResponseBuilder::nextRouteResponse(Routes::UPDATE_STEP_BACKUP);
    }

    /**
     * @throws \Exception
     */
    public function startUpdate(): JsonResponse
    {
        $updateConfiguration = $this->upgradeContainer->getUpdateConfiguration();
        $updateConfiguration->merge([UpgradeConfiguration::BACKUP_COMPLETED => false]);
        $this->upgradeContainer->getConfigurationStorage()->save($updateConfiguration);

        return AjaxResponseBuilder::nextRouteResponse(Routes::UPDATE_STEP_UPDATE);
    }

    public function saveOption(): JsonResponse
    {
        $configurationStorage = $this->upgradeContainer->getConfigurationStorage();
        $upgradeConfiguration = $this->upgradeContainer->getUpdateConfiguration();

        $config = [
            UpgradeConfiguration::PS_AUTOUP_KEEP_IMAGES => $this->request->request->getBoolean(UpgradeConfiguration::PS_AUTOUP_KEEP_IMAGES, false),
        ];

        $errors = $this->upgradeContainer->getConfigurationValidator()->validate($config);
        if (empty($errors)) {
            $upgradeConfiguration->merge($config);
            $configurationStorage->save($upgradeConfiguration);
        }

        return $this->getRefreshOfForm(array_merge(
            $this->getParams(),
            ['errors' => ValidatorToFormFormater::format($errors)]
        ));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $updateConfiguration = $this->upgradeContainer->getUpdateConfiguration();
        $updateSteps = new Stepper($this->upgradeContainer->getTranslator(), TaskType::TASK_TYPE_UPDATE);

        $logsPath = $this->upgradeContainer->getLogsService()->getDownloadLogsPath(TaskType::TASK_TYPE_BACKUP);

        return array_merge(
            $updateSteps->getStepParams($this::CURRENT_STEP),
            [
                'backup_completed' => $this->upgradeContainer->getUpdateConfiguration()->isBackupCompleted(),
                'download_path' => $logsPath,
                'filename' => basename($logsPath),
                'tracking_event' => Events::BACKUP_LOGS_DOWNLOADED,

                'form_route_to_save' => Routes::UPDATE_STEP_BACKUP_SAVE_OPTION,
                'form_route_to_submit_backup' => Routes::UPDATE_STEP_BACKUP_SUBMIT_BACKUP,
                'form_route_to_submit_update' => Routes::UPDATE_STEP_BACKUP_SUBMIT_UPDATE,

                'form_fields' => [
                    'include_images' => [
                        'field' => UpgradeConfiguration::PS_AUTOUP_KEEP_IMAGES,
                        'value' => $updateConfiguration->shouldBackupImages(),
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

    /**
     * @param array<string, mixed> $params
     */
    private function displayDialog(string $dialogName, array $params): JsonResponse
    {
        switch ($dialogName) {
            case 'dialog-update':
                $options = ['addScript' => 'start-update-dialog'];
                break;
            case 'dialog-backup':
                $options = ['addScript' => 'start-backup-dialog'];
                break;
            default:
                $options = null;
        }

        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::DIALOG_PARENT_ID,
            $this->getTwig()->render(
                '@ModuleAutoUpgrade/dialogs/' . $dialogName . '.html.twig',
                $params
            ),
            $options
        );
    }
}
