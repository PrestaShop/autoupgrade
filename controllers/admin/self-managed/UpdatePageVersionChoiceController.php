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

use Exception;
use PrestaShop\Module\AutoUpgrade\AjaxResponseBuilder;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use PrestaShop\Module\AutoUpgrade\Services\PhpVersionResolverService;
use PrestaShop\Module\AutoUpgrade\Task\Miscellaneous\UpdateConfig;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;
use PrestaShop\Module\AutoUpgrade\Twig\UpdateSteps;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\Upgrader;
use PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck;
use PrestaShop\Module\AutoUpgrade\VersionUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class UpdatePageVersionChoiceController extends AbstractPageController
{
    const CURRENT_PAGE = 'update';
    const CURRENT_ROUTE = Routes::UPDATE_PAGE_VERSION_CHOICE;
    const CURRENT_STEP = UpdateSteps::STEP_VERSION_CHOICE;
    const FORM_NAME = 'version_choice';
    const FORM_FIELDS = [
        'channel' => 'channel',
        'archive_zip' => 'archive_zip',
        'archive_xml' => 'archive_xml',
    ];
    const FORM_OPTIONS = [
        'online_value' => Upgrader::CHANNEL_ONLINE,
        'local_value' => Upgrader::CHANNEL_LOCAL,
    ];

    /**
     * @return string
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function step(): string
    {
        return $this->getTwig()->render(
            '@ModuleAutoUpgrade/steps/version-choice.html.twig',
            $this->getParams()
        );
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function getParams(): array
    {
        $updateSteps = new UpdateSteps($this->upgradeContainer->getTranslator());
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
        $archiveRepository = $this->upgradeContainer->getLocalArchiveRepository();

        $upgradeConfiguration = $this->upgradeContainer->getUpgradeConfiguration();
        $currentChannel = $upgradeConfiguration->getChannel();

        $params = array_merge(
            $updateSteps->getStepParams($this::CURRENT_STEP),
            [
                'up_to_date' => !$isNewerVersionAvailableOnline,
                'no_local_archive' => !$this->upgradeContainer->getLocalArchiveRepository()->hasLocalArchive(),
                'assets_base_path' => $this->upgradeContainer->getAssetsEnvironment()->getAssetsBaseUrl($this->request),
                'current_prestashop_version' => $this->getPsVersion(),
                'current_php_version' => VersionUtils::getHumanReadableVersionOf(PHP_VERSION_ID),
                'local_archives' => [
                    'zip' => $archiveRepository->getZipLocalArchive(),
                    'xml' => $archiveRepository->getXmlLocalArchive(),
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
                    self::FORM_FIELDS['channel'] => $currentChannel,
                    self::FORM_FIELDS['archive_zip'] => $upgradeConfiguration->getLocalChannelZip(),
                    self::FORM_FIELDS['archive_xml'] => $upgradeConfiguration->getLocalChannelXml(),
                ],
            ]
        );

        if ($currentChannel === self::FORM_OPTIONS['online_value'] ||
            (
                $currentChannel === self::FORM_OPTIONS['local_value']
                && in_array($upgradeConfiguration->getLocalChannelZip(), $archiveRepository->getZipLocalArchive())
                && in_array($upgradeConfiguration->getLocalChannelXml(), $archiveRepository->getXmlLocalArchive())
            )
        ) {
            $params[$currentChannel . '_requirements'] = $this->getRequirements();
        }

        return $params;
    }

    /**
     * @throws Exception
     */
    private function getRequirements(): array
    {
        $controller = new UpdateConfig($this->upgradeContainer);
        $controller->init();

        $distributionApiService = new DistributionApiService();
        $phpVersionResolverService = new PhpVersionResolverService(
            $distributionApiService,
            $this->upgradeContainer->getFileLoader(),
            $this->upgradeContainer->getState()->getOriginVersion()
        );

        $upgradeSelfCheck = new UpgradeSelfCheck(
            $this->upgradeContainer->getUpgrader(),
            $this->upgradeContainer->getPrestaShopConfiguration(),
            $this->upgradeContainer->getTranslator(),
            $phpVersionResolverService,
            $this->upgradeContainer->getChecksumCompare(),
            _PS_ROOT_DIR_,
            _PS_ADMIN_DIR_,
            $this->upgradeContainer->getProperty(UpgradeContainer::WORKSPACE_PATH),
            $this->upgradeContainer->getState()->getOriginVersion()
        );

        $warnings = $upgradeSelfCheck->getWarnings();
        foreach ($warnings as $warningKey => $warningValue) {
            $warnings[$warningKey] = $upgradeSelfCheck->getRequirementWording($warningKey);
        }

        $errors = $upgradeSelfCheck->getErrors();
        foreach ($errors as $errorKey => $errorValue) {
            $errors[$errorKey] = $upgradeSelfCheck->getRequirementWording($errorKey);
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

        $controller = new UpdateConfig($this->upgradeContainer);
        $controller->init();
        $controller->run();

        $params = array_merge(
            $this->getParams(),
            ['current_values' => $this->request->request->all()]
        );

        if ($channel === self::FORM_OPTIONS['local_value']) {
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
        return new JsonResponse([
            'next_route' => Routes::UPDATE_STEP_UPDATE_OPTIONS,
        ]);
    }
}
