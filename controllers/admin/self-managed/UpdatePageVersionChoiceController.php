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
        $onlineDestination = $this->upgradeContainer->getUpgrader()->getOnlineDestinationRelease();

        if ($isNewerVersionAvailableOnline) {
            $updateType = VersionUtils::getUpdateType($this->getPsVersion(), $onlineDestination->getVersion());
            $releaseNote = $this->upgradeContainer->getUpgrader()->getOnlineDestinationRelease()->getReleaseNoteUrl();
        } else {
            $updateType = null;
            $releaseNote = null;
        }

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

        $upgradeConfiguration = $this->upgradeContainer->getUpdateConfiguration();
        $localVersions = $this->upgradeContainer->getLocalVersionFilesService()->getFlatZipAndXmlLists();
        $noLocalArchive = empty($localVersions['zip']) && empty($localVersions['xml']);
        $currentPsVersion = $this->upgradeContainer->getProperty(UpgradeContainer::PS_VERSION);
        $currentMajorVersion = VersionUtils::splitPrestaShopVersion($currentPsVersion)['major'];

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
                'local_archives' => [
                    'zip' => $localVersions['zip'],
                    'xml' => $localVersions['xml'],
                ],
                'next_release' => [
                    'version' => $onlineDestination ? $onlineDestination->getVersion() : null,
                    'badge_label' => $updateLabel,
                    'badge_status' => $updateType,
                    'release_note' => $releaseNote,
                ],
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

            $configurationStorage = $this->upgradeContainer->getConfigurationStorage();

            $updateConfiguration = $this->upgradeContainer->getUpdateConfiguration();
            $updateConfiguration->merge($requestConfig);

            if (!$updateConfiguration->hasAllTheShopConfiguration()) {
                $this->upgradeContainer->getPrestaShopConfiguration()->fillInUpdateConfiguration($updateConfiguration);
            }

            $configurationStorage->save($updateConfiguration);

            if ($channel !== null) {
                $params[$channel . '_requirements'] = $this->getRequirements();
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

        return AjaxResponseBuilder::hydrationResponse(PageSelectors::RADIO_CARD_ONLINE_PARENT_ID, $this->getTwig()->render(
            '@ModuleAutoUpgrade/components/radio-card-online.html.twig',
            $params
        ));
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
            ['addScript' => 'tempered-files-dialog']
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
            ['addScript' => 'tempered-files-dialog']
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
