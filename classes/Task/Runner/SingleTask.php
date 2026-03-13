<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Task\Runner;

class SingleTask extends ChainedTasks
{
    /** @var bool */
    private $stepHasRun = false;

    public function setOptions(array $options): void
    {
        if (!empty($options['action'])) {
            $this->step = $options['action'];
        }
    }

    protected function canContinue(): bool
    {
        if (!$this->stepHasRun) {
            $this->stepHasRun = true;

            return true;
        }

        return false;
    }
}
