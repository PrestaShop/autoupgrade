<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Models;

class PrestashopRelease
{
    /** @var string */
    private $version;
    /** @var 'rc'|'beta'|'stable' */
    private $stability;
    /** @var 'open_source'|'classic' */
    private $distribution;
    /** @var ?string */
    private $phpMaxVersion;
    /** @var ?string */
    private $phpMinVersion;
    /** @var ?string */
    private $zipDownloadUrl;
    /** @var ?string */
    private $xmlDownloadUrl;
    /** @var ?string */
    private $zipMd5;
    /** @var ?string */
    private $releaseNoteUrl;
    /** @var ?string */
    private $distributionVersion;

    public function __construct(
        string $version,
        string $stability,
        ?string $distribution = null,
        ?string $phpMaxVersion = null,
        ?string $phpMinVersion = null,
        ?string $zipDownloadUrl = null,
        ?string $xmlDownloadUrl = null,
        ?string $zipMd5 = null,
        ?string $releaseNoteUrl = null,
        ?string $distributionVersion = null
    ) {
        $this->version = $version;
        $this->stability = $stability;
        $this->distribution = $distribution;
        $this->phpMaxVersion = $phpMaxVersion;
        $this->phpMinVersion = $phpMinVersion;
        $this->zipDownloadUrl = $zipDownloadUrl;
        $this->xmlDownloadUrl = $xmlDownloadUrl;
        $this->zipMd5 = $zipMd5;
        $this->releaseNoteUrl = $releaseNoteUrl;
        $this->distributionVersion = $distributionVersion;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getStability(): string
    {
        return $this->stability;
    }

    public function getDistribution(): string
    {
        return $this->distribution;
    }

    public function getPhpMaxVersion(): ?string
    {
        return $this->phpMaxVersion;
    }

    public function getPhpMinVersion(): ?string
    {
        return $this->phpMinVersion;
    }

    public function getZipDownloadUrl(): ?string
    {
        return $this->zipDownloadUrl;
    }

    public function getXmlDownloadUrl(): ?string
    {
        return $this->xmlDownloadUrl;
    }

    public function getZipMd5(): ?string
    {
        return $this->zipMd5;
    }

    public function getReleaseNoteUrl(): ?string
    {
        return $this->releaseNoteUrl;
    }

    public function getDistributionVersion(): ?string
    {
        return $this->distributionVersion;
    }
}
