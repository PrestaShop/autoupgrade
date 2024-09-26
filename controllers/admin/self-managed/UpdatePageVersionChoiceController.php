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

use PrestaShop\Module\AutoUpgrade\Router\Router;
use PrestaShop\Module\AutoUpgrade\Twig\UpdateSteps;
use PrestaShop\Module\AutoUpgrade\VersionUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use PrestaShop\Module\AutoUpgrade\Twig\PageSelectors;

class UpdatePageVersionChoiceController extends AbstractPageController
{
    const CURRENT_STEP = UpdateSteps::STEP_VERSION_CHOICE;
    const CURRENT_ROUTE = Router::UPDATE_PAGE_VERSION_CHOICE;
    const CURRENT_PAGE = 'update';

    /**
     * @param Request $request
     *
     * @return string
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function step(Request $request): string
    {
        return $this->twig->render(
            '@ModuleAutoUpgrade/steps/version-choice.html.twig',
            $this->getParams($request)
        );
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    protected function getParams(Request $request): array
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
                'assets_base_path' => $this->upgradeContainer->getAssetsEnvironment()->getAssetsBaseUrl($request),
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
            ]
        );
    }
}
