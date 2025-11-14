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

class Release
{
    /** @var string */
    public $compatibleFrom = '';
    /** @var string */
    public $compatibleTo = '';
    /** @var string */
    public $productVersion = '';
    /** @var string */
    public $releaseDate = '';
    /** @var array<int, string> */
    public $changeLogs = [];

    /**
     * @param array{
     *     compatible_from?: string,
     *     compatible_to?: string,
     *     product_version?: string,
     *     release_date?: string,
     *     change_logs?: array<int, string>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $obj = new self();

        $obj->compatibleFrom = $data['compatible_from'] ?? '';
        $obj->compatibleTo = $data['compatible_to'] ?? '';
        $obj->productVersion = $data['product_version'] ?? '';
        $obj->releaseDate = $data['release_date'] ?? '';
        $obj->changeLogs = $data['change_logs'] ?? [];

        return $obj;
    }
}
