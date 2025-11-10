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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source;

class MarketplaceModule
{
    /** @var int */
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $compatibilityFrom;
    /** @var string */
    private $compatibilityTo;
    /** @var string */
    private $version;

    /**
     * @param int $id
     * @param string $displayName
     * @param string $compatibilityFrom
     * @param string $compatibilityTo
     */
    public function __construct(int $id, string $name, string $compatibilityFrom, string $compatibilityTo, string $version)
    {
        $this->id = $id;
        $this->name = $name;
        $this->compatibilityFrom = $compatibilityFrom;
        $this->compatibilityTo = $compatibilityTo;
        $this->version = $version;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCompatibilityFrom(): string
    {
        return $this->compatibilityFrom;
    }

    /**
     * @return string
     */
    public function getCompatibilityTo(): string
    {
        return $this->compatibilityTo;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
