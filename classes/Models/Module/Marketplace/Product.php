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

class Product
{
    /** @var int */
    public $id;
    /** @var string */
    public $productType = '';
    /** @var bool */
    public $isNative = false;
    /** @var bool */
    public $downloadable = false;
    /** @var bool */
    public $isActive = false;

    /**
     * @param array{
     *     id_product?: int,
     *     product_type?: string,
     *     is_native?: bool,
     *     downloadable?: bool,
     *     is_active?: bool
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $obj = new self();

        $obj->id = (int) ($data['id_product'] ?? 0);
        $obj->productType = $data['product_type'] ?? '';
        $obj->isNative = (bool) ($data['is_native'] ?? false);
        $obj->downloadable = (bool) ($data['downloadable'] ?? false);
        $obj->isActive = (bool) ($data['is_active'] ?? false);

        return $obj;
    }
}
