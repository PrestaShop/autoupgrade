<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace;

class ModuleUpgradeCompatibility
{
    /** @var bool */
    private $isCompatible;

    /** @var bool */
    private $hasUpdateAvailable;

    /** @var Release|null */
    private $latestRelease;

    /** @var Release|null */
    private $compatibleRelease;

    public function __construct(
        bool $isCompatible,
        bool $hasUpdateAvailable,
        ?Release $latestRelease = null,
        ?Release $compatibleRelease = null
    ) {
        $this->isCompatible = $isCompatible;
        $this->hasUpdateAvailable = $hasUpdateAvailable;
        $this->latestRelease = $latestRelease;
        $this->compatibleRelease = $compatibleRelease;
    }

    public function isCompatible(): bool
    {
        return $this->isCompatible;
    }

    public function hasUpdateAvailable(): bool
    {
        return $this->hasUpdateAvailable;
    }

    public function getCompatibleRelease(): ?Release
    {
        return $this->compatibleRelease;
    }

    public function getLatestRelease(): ?Release
    {
        return $this->latestRelease;
    }
}
