<?php

namespace PrestaShop\Module\AutoUpgrade\Controller;

use Symfony\Component\HttpFoundation\Request;

class UpdatePageController extends AbstractPageController
{
    /**
     * @var array[]
     */
    private $steps = [
            'version-choice' => [
                'state' => 'normal',
                'title' => 'Version choice'
            ],
            'update-options' => [
                'state' => 'normal',
                'title' => 'Update options'
            ],
            'backup' => [
                'state' => 'normal',
                'title' => 'Backup'
            ],
            'update' => [
                'state' => 'normal',
                'title' => 'Update'
            ],
            'post-update' => [
                'state' => 'normal',
                'title' => 'Post-update'
            ]
        ];

    public function index(Request $request): string
    {
        $currentStep = $request->query->get('step') ?? 'version-choice';

        return $this->renderPage(
            'update',
            [
                'step' => $currentStep,
                'step_title' => $this->getStepName($currentStep),
                'steps' => $this->getSteps($currentStep)
            ]
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
        }

        return array_values($steps);
    }

    private function getStepName(string $step): string
    {
        return $this->steps[$step]['title'];
    }
}
