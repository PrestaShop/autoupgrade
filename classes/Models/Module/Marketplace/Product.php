<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
