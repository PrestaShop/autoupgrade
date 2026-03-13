<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
    /** @var bool */
    private $recommended;
    /** @var string|null */
    private $recommendedVersionChangelog;

    public function __construct(
        string $prestashopMinVersion,
        string $prestashopMaxVersion,
        string $recommendedVersion,
        string $recommendedVersionLink,
        string $recommendedVersionMd5,
        bool $recommended,
        ?string $recommendedVersionChangelog
    ) {
        $this->prestashopMinVersion = $prestashopMinVersion;
        $this->prestashopMaxVersion = $prestashopMaxVersion;
        $this->recommendedVersion = $recommendedVersion;
        $this->recommendedVersionLink = $recommendedVersionLink;
        $this->recommendedVersionMd5 = $recommendedVersionMd5;
        $this->recommendedVersionChangelog = $recommendedVersionChangelog;
        $this->recommended = $recommended;
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

    public function isRecommended(): bool
    {
        return $this->recommended;
    }
}
