<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace;

class Module
{
    /** @var Attributes */
    public $attributes;
    /** @var Compliance */
    public $compliance;
    /** @var Product */
    public $product;
    /** @var TechnicalInfo */
    public $technicalInfo;

    /**
     * @param array{
     *     attributes?: array<int, string>,
     *     compliance?: array<int, string>,
     *     product?: array<int, string>,
     *     technical_info?: array<int, string>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $obj = new self();

        $obj->attributes = Attributes::fromArray($data['attributes'] ?? []);
        $obj->compliance = Compliance::fromArray($data['compliance'] ?? []);
        $obj->product = Product::fromArray($data['product'] ?? []);
        $obj->technicalInfo = TechnicalInfo::fromArray($data['technical_info'] ?? []);

        return $obj;
    }
}
