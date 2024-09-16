<?php

namespace PrestaShop\Module\AutoUpgrade\Twig;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class UpdateSteps
{
    const VERSION_CHOICE = 'version-choice';
    const UPDATE_OPTIONS = 'update-options';
    const BACKUP = 'backup';
    const UPDATE = 'update';
    const POST_UPDATE = 'post-update';

    const STATE_NORMAL = 'normal';
    const STATE_CURRENT = 'current';
    const STATE_DONE = 'done';

    /** @var Translator */
    private $translator;

    /** @var array<string, array<string,string>> */
    private $steps;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
        $this->setSteps();
    }

    public function setSteps(): void
    {
        $this->steps = [
            self::VERSION_CHOICE => [
                'title' => $this->translator->trans('Version choice'),
            ],
            self::UPDATE_OPTIONS => [
                'title' => $this->translator->trans('Update options'),
            ],
            self::BACKUP => [
                'title' => $this->translator->trans('Backup'),
            ],
            self::UPDATE => [
                'title' => $this->translator->trans('Update'),
            ],
            self::POST_UPDATE => [
                'title' => $this->translator->trans('Post-update'),
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getSteps(string $currentStep): array
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

            unset($step['params']);
        }

        return array_values($steps);
    }

    public function getStepTitle(string $step): string
    {
        return $this->steps[$step]['title'];
    }
}
