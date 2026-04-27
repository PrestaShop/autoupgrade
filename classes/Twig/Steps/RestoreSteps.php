<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Twig\Steps;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class RestoreSteps implements StepsInterface
{
    const STEP_BACKUP_SELECTION = 'backup-selection';
    const STEP_RESTORE = 'restore';
    const STEP_POST_RESTORE = 'post-restore';

    /** @var Translator */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function getSteps(): array
    {
        return [
            self::STEP_BACKUP_SELECTION => [
                'title' => $this->translator->trans('Backup selection'),
            ],
            self::STEP_RESTORE => [
                'title' => $this->translator->trans('Restore'),
            ],
            self::STEP_POST_RESTORE => [
                'title' => $this->translator->trans('Post-restore'),
            ],
        ];
    }
}
