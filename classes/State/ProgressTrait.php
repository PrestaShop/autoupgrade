<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\State;

use InvalidArgumentException;

trait ProgressTrait
{
    /** @var int */
    protected $progressPercentage;

    public function getProgressPercentage(): ?int
    {
        return $this->progressPercentage;
    }

    public function setProgressPercentage(int $progressPercentage): self
    {
        if ($progressPercentage && $progressPercentage < $this->progressPercentage) {
            throw new InvalidArgumentException('Updated progress percentage cannot be lower than the currently set one.');
        }

        $this->progressPercentage = $progressPercentage;
        $this->save();

        return $this;
    }
}
