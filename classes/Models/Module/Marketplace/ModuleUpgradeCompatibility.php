<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
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
