<?php

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\Twig\UpdateSteps;
use PrestaShop\Module\AutoUpgrade\VersionUtils;
use Symfony\Component\HttpFoundation\Request;

class UpdateVersionChoicePageController extends AbstractPageController
{
    const CURRENT_STEP = UpdateSteps::VERSION_CHOICE;

    /**
     * @param Request $request
     *
     * @return string
     *
     * @throws \Exception
     */
    public function index(Request $request): string
    {
        return $this->renderPage(
            'update',
            $this->getParams()
        );
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    private function getParams(): array
    {
        $updateSteps = new UpdateSteps($this->upgradeContainer->getTranslator());

        return [
            'step' => [
                'code' => $this::CURRENT_STEP,
                'title' => $updateSteps->getStepTitle($this::CURRENT_STEP),
            ],
            'steps' => $updateSteps->getSteps($this::CURRENT_STEP),
            'upToDate' => false /* TODO */ ,
            'noLocalArchive' => !$this->upgradeContainer->getLocalArchiveRepository()->hasLocalArchive(),
            'psBaseUri' => __PS_BASE_URI__,
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
