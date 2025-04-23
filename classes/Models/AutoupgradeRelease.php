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

namespace PrestaShop\Module\AutoUpgrade\Models;

class AutoupgradeRelease
{
    /** @var string */
    private $prestashopMinVersion;
    /** @var string */
    private $prestashopMaxVersion;
    /** @var string */
    private $recommendedVersion;
    /** @var string */
    private $recommendedVersionLink;
    /** @var string */
    private $recommendedVersionMd5;
    /** @var string|null */
    private $recommendedVersionChangelog;

    public function __construct(
        string $prestashopMinVersion,
        string $prestashopMaxVersion,
        string $recommendedVersion,
        string $recommendedVersionLink,
        string $recommendedVersionMd5,
        ?string $recommendedVersionChangelog
    ) {
        $this->prestashopMinVersion = $prestashopMinVersion;
        $this->prestashopMaxVersion = $prestashopMaxVersion;
        $this->recommendedVersion = $recommendedVersion;
        $this->recommendedVersionLink = $recommendedVersionLink;
        $this->recommendedVersionMd5 = $recommendedVersionMd5;
        $this->recommendedVersionChangelog = $recommendedVersionChangelog;
    }

    public function getPrestashopMinVersion(): string
    {
        return $this->prestashopMinVersion;
    }

    public function getPrestashopMaxVersion(): string
    {
        return $this->prestashopMaxVersion;
    }

    public function getRecommendedVersion(): string
    {
        return $this->recommendedVersion;
    }

    public function getRecommendedVersionLink(): string
    {
        return $this->recommendedVersionLink;
    }

    public function getRecommendedVersionMd5(): string
    {
        return $this->recommendedVersionMd5;
    }

    public function getRecommendedVersionChangelog(): ?string
    {
        return $this->recommendedVersionChangelog;
    }
}
