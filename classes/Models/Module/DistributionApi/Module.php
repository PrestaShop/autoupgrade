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

namespace PrestaShop\Module\AutoUpgrade\Models\Module\DistributionApi;

class Module
{
    /** @var string */
    private $name;
    /** @var string|null */
    private $displayName;
    /** @var string|null */
    private $tab;
    /** @var string|null */
    private $description;
    /** @var string|null */
    private $author;
    /** @var string */
    private $version;
    /** @var string|null */
    private $prestashopMinVersion;
    /** @var string|null */
    private $prestashopMaxVersion;
    /** @var string */
    private $downloadUrl;
    /** @var string */
    private $icon;

    public function __construct(
        string $name,
        string $version,
        string $downloadUrl,
        string $icon,
        ?string $displayName,
        ?string $tab,
        ?string $description,
        ?string $author,
        ?string $prestashopMinVersion,
        ?string $prestashopMaxVersion
    ) {
        $this->name = $name;
        $this->displayName = $displayName;
        $this->tab = $tab;
        $this->description = $description;
        $this->author = $author;
        $this->version = $version;
        $this->prestashopMinVersion = $prestashopMinVersion;
        $this->prestashopMaxVersion = $prestashopMaxVersion;
        $this->downloadUrl = $downloadUrl;
        $this->icon = $icon;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function getTab(): ?string
    {
        return $this->tab;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getPrestashopMinVersion(): ?string
    {
        return $this->prestashopMinVersion;
    }

    public function getPrestashopMaxVersion(): ?string
    {
        return $this->prestashopMaxVersion;
    }

    public function getDownloadUrl(): string
    {
        return $this->downloadUrl;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }
}
