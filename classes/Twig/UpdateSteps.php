<?php

namespace PrestaShop\Module\AutoUpgrade\Twig;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class UpdateSteps
{
    const STEP_VERSION_CHOICE = 'version-choice';
    const STEP_UPDATE_OPTIONS = 'update-options';
    const STEP_BACKUP = 'backup';
    const STEP_UPDATE = 'update';
    const STEP_POST_UPDATE = 'post-update';

    const STATE_NORMAL = 'normal';
    const STATE_CURRENT = 'current';
    const STATE_DONE = 'done';

    /** @var Translator */
    private $translator;

    /** @var array<self::STEP_*, array<string,string>> */
    private $steps;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
        $this->setSteps();
    }

    public function setSteps(): void
    {
        $this->steps = [
            self::STEP_VERSION_CHOICE => [
                'title' => $this->translator->trans('Version choice'),
            ],
            self::STEP_UPDATE_OPTIONS => [
                'title' => $this->translator->trans('Update options'),
            ],
            self::STEP_BACKUP => [
                'title' => $this->translator->trans('Backup'),
            ],
            self::STEP_UPDATE => [
                'title' => $this->translator->trans('Update'),
            ],
            self::STEP_POST_UPDATE => [
                'title' => $this->translator->trans('Post-update'),
            ],
        ];
    }

    /**
     * @param self::STEP_* $currentStep
     *
     * @return array<int, array<string, string>>
     */
    public function getSteps($currentStep): array
    {
        $steps = $this->steps;

        $foundCurrentStep = false;

        foreach ($steps as $key => &$step) {
            if ($key === $currentStep) {
                $step['state'] = $this::STATE_CURRENT;
                $foundCurrentStep = true;
            } elseif (!$foundCurrentStep) {
                $step['state'] = $this::STATE_DONE;
            } else {
                $step['state'] = $this::STATE_NORMAL;
            }

            $step['code'] = $key;
        }

        return array_values($steps);
    }

    public function getStepTitle(string $step): string
    {
        return $this->steps[$step]['title'];
    }

    /**
     * @return array{
     *                step: array{
     *                code: string,
     *                title: string
     *                },
     *                steps: array<int, array<string, string>>
     *                }
     */
    public function getStepParams(string $step)
    {
        return [
            'step' => [
                'code' => $step,
                'title' => $this->getStepTitle($step),
            ],
            'steps' => $this->getSteps($step),
        ];
    }
}
