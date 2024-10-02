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

use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Twig\UpdateSteps;
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
    const FORM_FIELDS = [
        'canal_choice' => 'canal_choice',
        'archive_zip' => 'archive_zip',
        'archive_xml' => 'archive_xml',
    ];
    const FORM_OPTIONS = [
        'online_value' => 'online',
        'local_value' => 'local',
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
        return $this->twig->render(
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
        $isLastVersion = $this->upgradeContainer->getUpgrader()->isLastVersion();

        if (!$isLastVersion) {
            $updateType = VersionUtils::getUpdateType($this->getPsVersion(), $this->upgradeContainer->getUpgrader()->getDestinationVersion());
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

        return array_merge(
            $updateSteps->getStepParams($this::CURRENT_STEP),
            [
                'up_to_date' => $isLastVersion,
                'no_local_archive' => !$this->upgradeContainer->getLocalArchiveRepository()->hasLocalArchive(),
                'assets_base_path' => $this->upgradeContainer->getAssetsEnvironment()->getAssetsBaseUrl($this->request),
                'current_prestashop_version' => $this->getPsVersion(),
                'current_php_version' => VersionUtils::getHumanReadableVersionOf(PHP_VERSION_ID),
                'local_archives' => [
                    'zip' => $archiveRepository->getZipLocalArchive(),
                    'xml' => $archiveRepository->getXmlLocalArchive(),
                ],
                'next_release' => [
                    'version' => $this->upgradeContainer->getUpgrader()->getDestinationVersion(),
                    'badge_label' => $updateLabel,
                    'badge_status' => $updateType,
                    'release_note' => $releaseNote,
                ],
                'form_route_to_save' => Routes::UPDATE_STEP_VERSION_CHOICE_SAVE_FORM,
                'form_route_to_submit' => Routes::UPDATE_STEP_VERSION_CHOICE_SUBMIT_FORM,
                'form_fields' => self::FORM_FIELDS,
                'form_options' => self::FORM_OPTIONS,
            ]
        );
    }

    public function save()
    {
    }

    public function submit()
    {
        /* todo: check everything is ok before send next route */
        return new JsonResponse([
            'next_route' => Routes::UPDATE_STEP_UPDATE_OPTIONS,
        ]);
    }
}
