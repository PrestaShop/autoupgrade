<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace;

class Compliance
{
    /** @var Compatibility */
    public $compatibility;
    /** @var Overrides */
    public $overrides;

    /**
     * @param array{
     *     compatibility?: array<int, string>,
     *     overrides?: array<int, string>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $obj = new self();

        $obj->compatibility = Compatibility::fromArray($data['compatibility'] ?? []);
        $obj->overrides = Overrides::fromArray($data['overrides'] ?? []);

        return $obj;
    }
}
