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
use Exception;
use PrestaShop\Module\AutoUpgrade\AjaxResponseBuilder;
use PrestaShop\Module\AutoUpgrade\DocumentationLinks;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\Stepper;
use PrestaShop\Module\AutoUpgrade\Twig\Steps\UpdateSteps;
use PrestaShop\Module\AutoUpgrade\Twig\ValidatorToFormFormater;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\VersionUtils;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdatePageVersionChoiceController extends AbstractPageWithStepController
{
    const CURRENT_STEP = UpdateSteps::STEP_VERSION_CHOICE;
    const FORM_NAME = 'version_choice';
    const FORM_FIELDS = [
        UpgradeConfiguration::CHANNEL => UpgradeConfiguration::CHANNEL,
        UpgradeConfiguration::ARCHIVE_ZIP => UpgradeConfiguration::ARCHIVE_ZIP,
        UpgradeConfiguration::ARCHIVE_XML => UpgradeConfiguration::ARCHIVE_XML,
    ];
    const FORM_OPTIONS = [
        'online_value' => UpgradeConfiguration::CHANNEL_ONLINE,
        'online_recommended_value' => UpgradeConfiguration::CHANNEL_ONLINE_RECOMMENDED,
        'local_value' => UpgradeConfiguration::CHANNEL_LOCAL,
    ];

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
        return Routes::UPDATE_PAGE_VERSION_CHOICE;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $updateSteps = new Stepper($this->upgradeContainer->getTranslator(), TaskType::TASK_TYPE_UPDATE);
        $isNewerVersionAvailableOnline = $this->upgradeContainer->getUpgrader()->isNewerVersionAvailableOnline();
        $recommendedOnlineDestination = null;
        $maxOnlineDestination = null;
        $nextReleases = [];

        if ($isNewerVersionAvailableOnline) {
            $recommendedOnlineDestination = $this->upgradeContainer->getUpgrader()->getOnlineRecommendedDestinationRelease();

            if ($recommendedOnlineDestination) {
                $recommendedOnlineDestinationUpdateType = VersionUtils::getUpdateType($this->getPsVersion(), $recommendedOnlineDestination->getVersion());
                $recommendedOnlineDestinationReleaseNote = $this->upgradeContainer->getUpgrader()->getOnlineRecommendedDestinationRelease()->getReleaseNoteUrl();
                $recommendedUpdateLabel = $this->getUpdateTypeLabel($recommendedOnlineDestinationUpdateType);
                $nextReleases['online_recommended'] = [
                    'version' => $recommendedOnlineDestination->getVersion(),
                    'badge_label' => $recommendedUpdateLabel,
                    'badge_status' => $recommendedOnlineDestinationUpdateType,
                    'release_note' => $recommendedOnlineDestinationReleaseNote,
                    'recommended' => true,
                    'message' => $this->upgradeContainer->getTranslator()->trans('The recommended version of PrestaShop to which you can update your store, based on its PHP version.'),
                ];
            }

            $maxOnlineDestination = $this->upgradeContainer->getUpgrader()->getOnlineMaxDestinationRelease();

            if ($maxOnlineDestination && ($recommendedOnlineDestination === null || $maxOnlineDestination->getVersion() !== $recommendedOnlineDestination->getVersion())) {
                $maxOnlineDestinationUpdateType = VersionUtils::getUpdateType($this->getPsVersion(), $maxOnlineDestination->getVersion());
                $maxOnlineDestinatioReleaseNote = $this->upgradeContainer->getUpgrader()->getOnlineMaxDestinationRelease()->getReleaseNoteUrl();
                $maxUpdateLabel = $this->getUpdateTypeLabel($maxOnlineDestinationUpdateType);
                $nextReleases['online'] = [
                    'version' => $maxOnlineDestination->getVersion(),
                    'badge_label' => $maxUpdateLabel,
                    'badge_status' => $maxOnlineDestinationUpdateType,
                    'release_note' => $maxOnlineDestinatioReleaseNote,
                    'recommended' => false,
                    'message' => $this->upgradeContainer->getTranslator()->trans('The maximum version of PrestaShop to which you can update your store, based on its PHP version.'),
                ];
            }
        }

        $upgradeConfiguration = $this->upgradeContainer->getUpdateConfiguration();
        $localVersions = $this->upgradeContainer->getLocalVersionFilesService()->getFlatZipAndXmlLists();
        $noLocalArchive = empty($localVersions['zip']) && empty($localVersions['xml']);
        $currentPsVersion = $this->upgradeContainer->getProperty(UpgradeContainer::PS_VERSION);
        $currentMajorVersion = VersionUtils::splitPrestaShopVersion($currentPsVersion)['major'];
        $currentUpdateAssistantMinorVersion = VersionUtils::splitPrestaShopVersion($this->upgradeContainer->getPrestaShopConfiguration()->getModuleVersion())['minor'];

        return array_merge(
            $updateSteps->getStepParams($this::CURRENT_STEP),
            [
                'dev_doc_upgrade_web_url' => DocumentationLinks::getDevDocUpdateAssistantWebUrl($currentMajorVersion),
                'up_to_date' => !$isNewerVersionAvailableOnline,
                'no_local_archive' => $noLocalArchive,
                // TODO: assets_base_path is provided by all controllers. What about a asset() twig function instead?
                'assets_base_path' => $this->upgradeContainer->getAssetsEnvironment()->getAssetsBaseUrl($this->request),
                'current_prestashop_version' => $this->getPsVersion(),
                'current_php_version' => VersionUtils::getHumanReadableVersionOf(PHP_VERSION_ID),
                'known_issues_discussions_url' => DocumentationLinks::getDiscussionsAboutKnownIssuesUrl($currentUpdateAssistantMinorVersion),
                'local_archives' => [
                    'zip' => $localVersions['zip'],
                    'xml' => $localVersions['xml'],
                ],
                'next_releases' => $nextReleases,
                'form_version_choice_name' => self::FORM_NAME,
                'form_route_to_save' => Routes::UPDATE_STEP_VERSION_CHOICE_SAVE_FORM,
                'form_route_to_submit' => Routes::UPDATE_STEP_VERSION_CHOICE_SUBMIT_FORM,
                'form_fields' => self::FORM_FIELDS,
                'form_options' => self::FORM_OPTIONS,
                'current_values' => [
                    self::FORM_FIELDS['channel'] => $upgradeConfiguration->getChannel(),
                    self::FORM_FIELDS['archive_zip'] => $upgradeConfiguration->getLocalChannelZip(),
                    self::FORM_FIELDS['archive_xml'] => $upgradeConfiguration->getLocalChannelXml(),
                ],
            ]
        );
    }

    private function getUpdateTypeLabel(string $updateType): ?string
    {
        switch ($updateType) {
            case 'major':
                $updateLabel = $this->upgradeContainer->getTranslator()->trans('Major version');
                break;
            case 'minor':
                $updateLabel = $this->upgradeContainer->getTranslator()->trans('Minor version');
                break;
            case 'patch':
                $updateLabel = $this->upgradeContainer->getTranslator()->trans('Patch version');
                break;
            default:
                $updateLabel = null;
        }

        return $updateLabel;
    }

    /**
     * @return array{
     *                'requirements_ok': bool,
     *                'warnings':array<int, array{'message': string, 'list'?: array<string>}>,
     *                'errors':array<int, array{'message': string, 'list'?: array<string>}>}
     *
     * @throws Exception
     */
    private function getRequirements(): array
    {
        $upgradeSelfCheck = $this->upgradeContainer->getUpgradeSelfCheck();

        $warnings = $upgradeSelfCheck->getWarnings();
        foreach ($warnings as $warningKey => $warningValue) {
            $warnings[$warningKey] = $upgradeSelfCheck->getRequirementWording($warningKey, true);
        }

        $errors = $upgradeSelfCheck->getErrors();
        foreach ($errors as $errorKey => $errorValue) {
            $errors[$errorKey] = $upgradeSelfCheck->getRequirementWording($errorKey, true);
        }

        return [
            'requirements_ok' => empty($errors),
            'warnings' => $warnings,
            'errors' => $errors,
        ];
    }

    /**
     * @throws Exception
     */
    public function save(): JsonResponse
    {
        $channel = $this->request->get(self::FORM_FIELDS['channel']);
        $isLocal = $channel === self::FORM_OPTIONS['local_value'];

        $requestConfig = $this->request->request->all();

        $this->upgradeContainer->initPrestaShopCore();

        $errors = $this->upgradeContainer->getConfigurationValidator()->validate($requestConfig);

        if ($isLocal && empty($errors)) {
            $errors = $this->upgradeContainer->getLocalChannelConfigurationValidator()->validate($requestConfig);
        }

        $params = $this->getParams();

        if (empty($errors)) {
            if ($isLocal) {
                $file = $requestConfig[UpgradeConfiguration::ARCHIVE_ZIP];
                $fullFilePath = $this->upgradeContainer->getProperty(UpgradeContainer::DOWNLOAD_PATH) . DIRECTORY_SEPARATOR . $file;
                $requestConfig[UpgradeConfiguration::ARCHIVE_VERSION_NUM] = $this->upgradeContainer->getPrestashopVersionService()->extractPrestashopVersionFromZip($fullFilePath);
            }

            switch ($channel) {
                case UpgradeConfiguration::CHANNEL_LOCAL:
                    $destinationVersion = $requestConfig[UpgradeConfiguration::ARCHIVE_VERSION_NUM];
                    break;
                case UpgradeConfiguration::CHANNEL_ONLINE:
                    $destinationVersion = $this->upgradeContainer->getUpgrader()->getOnlineMaxDestinationRelease()->getVersion();
                    break;
                case UpgradeConfiguration::CHANNEL_ONLINE_RECOMMENDED:
                    $destinationVersion = $this->upgradeContainer->getUpgrader()->getOnlineRecommendedDestinationRelease()->getVersion();
                    break;
            }

            if (isset($destinationVersion)) {
                $requestConfig[UpgradeConfiguration::UPDATE_TYPE] = VersionUtils::getUpdateType($this->getPsVersion(), $destinationVersion);
            }

            $configurationStorage = $this->upgradeContainer->getConfigurationStorage();

            $updateConfiguration = $this->upgradeContainer->getUpdateConfiguration();
            $updateConfiguration->merge($requestConfig);

            if (!$updateConfiguration->hasAllTheShopConfiguration()) {
                $this->upgradeContainer->getPrestaShopConfiguration()->fillInUpdateConfiguration($updateConfiguration);
            }

            $configurationStorage->save($updateConfiguration);

            if ($channel !== null) {
                $params['requirements'] = $this->getRequirements();
            }
        }

        $params = array_merge(
            $params,
            [
                'current_values' => $requestConfig,
                'errors' => ValidatorToFormFormater::format($errors),
            ]
        );

        if ($isLocal) {
            return AjaxResponseBuilder::hydrationResponse(PageSelectors::RADIO_CARD_ARCHIVE_PARENT_ID, $this->getTwig()->render(
                '@ModuleAutoUpgrade/components/radio-card-local.html.twig',
                $params
            ));
        }

        if ($channel === UpgradeConfiguration::CHANNEL_ONLINE) {
            $params['next_release'] = $params['next_releases']['online'];
            $params['release_type'] = 'online';
            $params['form_option_online_value'] = self::FORM_OPTIONS['online_value'];

            return AjaxResponseBuilder::hydrationResponse(PageSelectors::RADIO_CARD_ONLINE_PARENT_ID, $this->getTwig()->render(
            '@ModuleAutoUpgrade/components/radio-card-online.html.twig',
            $params
        ));
        } else {
            $params['next_release'] = $params['next_releases']['online_recommended'];
            $params['release_type'] = 'online_recommended';
            $params['form_option_online_value'] = self::FORM_OPTIONS['online_recommended_value'];

            return AjaxResponseBuilder::hydrationResponse(PageSelectors::RADIO_CARD_ONLINE_RECOMMENDED_PARENT_ID, $this->getTwig()->render(
            '@ModuleAutoUpgrade/components/radio-card-online.html.twig',
            $params
        ));
        }
    }

    public function submit(): JsonResponse
    {
        /* we dont check again because the button is only accessible if check are ok */
        return AjaxResponseBuilder::nextRouteResponse(Routes::UPDATE_STEP_UPDATE_OPTIONS);
    }

    public function coreTemperedFilesDialog(): JsonResponse
    {
        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::DIALOG_PARENT_ID,
            $this->getTemperedFilesDialog([
                'title' => $this->upgradeContainer->getTranslator()->trans('List of core alterations'),
                'message' => $this->upgradeContainer->getTranslator()->trans('Some core files have been altered, customization made on these files will be lost during the update.'),
                'container_id' => PageSelectors::TEMPERED_FILES_CONTAINER_ID,
                'content_action' => Routes::UPDATE_STEP_VERSION_CHOICE_CORE_TEMPERED_FILES_CONTENT,
            ]),
            ['addScript' => 'skeleton-dialog']
        );
    }

    public function coreTemperedFilesContent(): JsonResponse
    {
        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::TEMPERED_FILES_CONTAINER_ID,
            $this->getTemperedFilesDialogContent([
                'missing_files' => $this->upgradeContainer->getUpgradeSelfCheck()->getCoreMissingFiles(),
                'altered_files' => $this->upgradeContainer->getUpgradeSelfCheck()->getCoreAlteredFiles(),
            ])
        );
    }

    public function moduleReportDialog(): JsonResponse
    {
        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::DIALOG_PARENT_ID,
            $this->getTwig()->render(
                '@ModuleAutoUpgrade/dialogs/dialog-modules-report.html.twig',
                [
                    'title' => $this->upgradeContainer->getTranslator()->trans('Some modules require your attention'),
                    'container_id' => PageSelectors::MODULES_REPORT_CONTAINER_ID,
                    'content_action' => Routes::UPDATE_STEP_VERSION_CHOICE_MODULES_REPORT_CONTENT,
                ]
            ),
            ['addScript' => 'skeleton-dialog']
        );
    }

    public function moduleReportContent(): JsonResponse
    {
        $this->upgradeContainer->initPrestaShopCore();

        // All the different versions of PrestaShop require a different controller name to the Module Manager.
        // The existing names in the database plus the management of redirect requires us to use different values, because:
        // - With AdminModulesSf on PS 1.7, we get the proper module/manage, then AdminLogin without the next parameter,
        // - With AdminModules on PS 9, we get the AdminLogin login page with the next paramater, but then a missing controller error.
        $destinationController = version_compare($this->upgradeContainer->getProperty(UpgradeContainer::PS_VERSION), '9', '>=')
            ? 'AdminModulesSf' : 'AdminModules';
        $moduleManagerLink = Context::getContext()->link->getAdminLink($destinationController);

        $modulesRequiringAttention = $this->upgradeContainer->getUpgradeSelfCheck()->getModulesRequiringAttention();

        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::MODULES_REPORT_CONTAINER_ID,
            $this->getTwig()->render(
                '@ModuleAutoUpgrade/dialogs/dialog-modules-report-content.html.twig',
                [
                    'incompatible_modules' => $modulesRequiringAttention['incompatible_modules'],
                    'uncertain_modules' => $modulesRequiringAttention['uncertain_modules'],
                    'prestashop_version' => $this->upgradeContainer->getUpgrader()->getDestinationVersion(),
                    'module_manager_url' => $moduleManagerLink,
                ]
            )
        );
    }

    public function themeTemperedFilesDialog(): JsonResponse
    {
        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::DIALOG_PARENT_ID,
            $this->getTemperedFilesDialog([
                'title' => $this->upgradeContainer->getTranslator()->trans('List of theme alterations'),
                'message' => $this->upgradeContainer->getTranslator()->trans('Some theme files have been altered, customization made on these files will be lost during the update.'),
                'container_id' => PageSelectors::TEMPERED_FILES_CONTAINER_ID,
                'content_action' => Routes::UPDATE_STEP_VERSION_CHOICE_THEME_TEMPERED_FILES_CONTENT,
            ]),
            ['addScript' => 'skeleton-dialog']
        );
    }

    public function themeTemperedFilesContent(): JsonResponse
    {
        return AjaxResponseBuilder::hydrationResponse(
            PageSelectors::TEMPERED_FILES_CONTAINER_ID,
            $this->getTemperedFilesDialogContent([
                'missing_files' => $this->upgradeContainer->getUpgradeSelfCheck()->getThemeMissingFiles(),
                'altered_files' => $this->upgradeContainer->getUpgradeSelfCheck()->getThemeAlteredFiles(),
            ])
        );
    }

    /**
     * @param array<string,string|string[]> $params
     */
    private function getTemperedFilesDialog($params): string
    {
        return $this->getTwig()->render(
            '@ModuleAutoUpgrade/dialogs/dialog-tempered-files.html.twig',
            $params
        );
    }

    /**
     * @param array<string,string|string[]> $params
     */
    private function getTemperedFilesDialogContent($params): string
    {
        return $this->getTwig()->render(
            '@ModuleAutoUpgrade/dialogs/dialog-tempered-files-content.html.twig',
            $params
        );
    }
}
