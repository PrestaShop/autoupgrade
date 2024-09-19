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

use PrestaShop\Module\AutoUpgrade\Twig\UpdateSteps;
use PrestaShop\Module\AutoUpgrade\VersionUtils;
use Symfony\Component\HttpFoundation\Request;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class UpdatePageVersionChoiceController extends AbstractPageController
{
    const CURRENT_STEP = UpdateSteps::STEP_VERSION_CHOICE;
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

        return [
            'step' => [
                'code' => $this::CURRENT_STEP,
                'title' => $updateSteps->getStepTitle($this::CURRENT_STEP),
            ],
            'steps' => $updateSteps->getSteps($this::CURRENT_STEP),
            'upToDate' => true /* TODO */ ,
            'noLocalArchive' => !$this->upgradeContainer->getLocalArchiveRepository()->hasLocalArchive(),
            'assetsBasePath' => $this->upgradeContainer->getAssetsEnvironment()->getAssetsBaseUrl($request),
            'currentPrestashopVersion' => $this->getPsVersion(),
            'currentPhpVersion' => VersionUtils::getHumanReadableVersionOf(PHP_VERSION_ID),
            // TODO
            'nextRelease' => [
                'version' => '9.0.0',
                'releaseDate' => '01/05/2024',
                'badgeLabel' => 'Major version',
                'badgeStatus' => 'major',
                'releaseNote' => 'https://github.com/PrestaShop/autoupgrade',
            ],
        ];
    }
}
