<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\AutoUpgrade\Models;

class PrestashopRelease
{
    /** @var string */
    private $version;
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
    /** @var 'rc'|'beta'|'stable' */
    private $stability;

    public function __construct(
        string $version,
        string $stability,
        ?string $phpMaxVersion = null,
        ?string $phpMinVersion = null,
        ?string $zipDownloadUrl = null,
        ?string $xmlDownloadUrl = null,
        ?string $zipMd5 = null,
        ?string $releaseNoteUrl = null
    ) {
        $this->version = $version;
        $this->phpMaxVersion = $phpMaxVersion;
        $this->phpMinVersion = $phpMinVersion;
        $this->zipDownloadUrl = $zipDownloadUrl;
        $this->xmlDownloadUrl = $xmlDownloadUrl;
        $this->zipMd5 = $zipMd5;
        $this->releaseNoteUrl = $releaseNoteUrl;
        $this->stability = $stability;
    }

    public function getVersion(): string
    {
        return $this->version;
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

    public function getStability(): string
    {
        return $this->stability;
    }

    public function getReleaseNoteUrl(): ?string
    {
        return $this->releaseNoteUrl;
    }

    public function setReleaseNoteUrl(?string $releaseNoteUrl): void
    {
        $this->releaseNoteUrl = $releaseNoteUrl;
    }
}
