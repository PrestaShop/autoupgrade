<?php

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\VersionUtils;
use Symfony\Component\HttpFoundation\Request;

class UpdatePageController extends AbstractPageController
{
    /**
     * @var array[]
     */
    private $steps = [
            'version-choice' => [
                'state' => 'normal',
                'title' => 'Version choice',
                'params' => [
                    'upToDate',
                    'noLocalArchive',
                    'psBaseUri',
                    'currentPrestashopVersion',
                    'currentPhpVersion',
                ]
            ],
            'update-options' => [
                'state' => 'normal',
                'title' => 'Update options',
                'params' => []
            ],
            'backup' => [
                'state' => 'normal',
                'title' => 'Backup',
                'params' => []
            ],
            'update' => [
                'state' => 'normal',
                'title' => 'Update',
                'params' => []
            ],
            'post-update' => [
                'state' => 'normal',
                'title' => 'Post-update',
                'params' => []
            ]
        ];

    public function index(Request $request): string
    {
        $currentStep = $request->query->get('step') ?? 'version-choice';

        $params = [
            'step' => [
                'code' => $currentStep,
                'title' => $this->getStepName($currentStep),
            ],
            'steps' => $this->getSteps($currentStep)
        ];

        $params = array_merge($params, $this->getStepParams($currentStep));

        return $this->renderPage(
            'update',
            $params
        );
    }

    /**
     * @return array[]
     */
    private function getSteps(string $currentStep): array
    {
        $steps = $this->steps;

        $foundCurrentStep = false;

        foreach ($steps as $key => & $step) {
            if ($key === $currentStep) {
                $step['state'] = 'current';
                $foundCurrentStep = true;
            } elseif (!$foundCurrentStep) {
                $step['state'] = 'done';
            } else {
                $step['state'] = 'normal';
            }

            unset($step['params']);
        }

        return array_values($steps);
    }

    /**
     * @return array[]
     */
    private function getStepParams(string $step): array
    {
        if (!isset($this->steps[$step]['params'])) {
            return [];
        }

        $inputParams = $this->steps[$step]['params'];
        $outputParams = [];
        foreach ($inputParams as $param) {
            switch ($param) {
                case $param === 'currentPrestashopVersion':
                    $outputParams[$param] = $this->upgradeContainer->getProperty($this->upgradeContainer::PS_VERSION);
                    break;
                case $param === 'currentPhpVersion':
                    $outputParams[$param] = VersionUtils::getHumanReadableVersionOf(PHP_VERSION_ID);
                    break;
                case $param === 'upToDate':
                    // TODO
                    $outputParams[$param] = true;
                    break;
                case $param === 'noLocalArchive':
                    // TODO
                    $outputParams[$param] = false;
                    break;
                case $param === 'psBaseUri':
                    $outputParams[$param] = __PS_BASE_URI__;
                    break;
                default:
                    $outputParams[$param] = null;
            }
        }

        return $outputParams;
    }

    /**
     * @return string
     */
    private function getStepName(string $step): string
    {
        return $this->steps[$step]['title'];
    }
}
