<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Twig\Steps;

use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class Stepper
{
    /** @var Translator */
    private $translator;

    /** @var TaskType::TASK_* */
    private $currentTask;

    const STATE_NORMAL = 'normal';
    const STATE_CURRENT = 'current';
    const STATE_DONE = 'done';

    public function __construct(Translator $translator, string $currentTask)
    {
        $this->currentTask = TaskType::fromString($currentTask);
        $this->translator = $translator;
    }

    /**
     * @return array<UpdateSteps::STEP_* | RestoreSteps::STEP_*, array<string,string>>
     */
    private function getCurrentTaskSteps(): array
    {
        switch ($this->currentTask) {
            case TaskType::TASK_TYPE_UPDATE:
                return (new UpdateSteps($this->translator))->getSteps();
            case TaskType::TASK_TYPE_RESTORE:
                return (new RestoreSteps($this->translator))->getSteps();
            default:
                return [];
        }
    }

    /**
     * @param UpdateSteps::STEP_*|RestoreSteps::STEP_* $currentStep
     *
     * @return array<int, array<string, string>>
     */
    public function getSteps($currentStep): array
    {
        $steps = $this->getCurrentTaskSteps();

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

    /**
     * @param UpdateSteps::STEP_*|RestoreSteps::STEP_* $step
     *
     * @return string
     */
    public function getStepTitle($step): string
    {
        return $this->getCurrentTaskSteps()[$step]['title'];
    }

    /**
     * @return array{step: array{code: string, title: string}, steps: array<int, array<string, string>>}
     */
    public function getStepParams(string $step): array
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
