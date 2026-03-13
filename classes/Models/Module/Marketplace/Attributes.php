<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace;

class Attributes
{
    /** @var string[] */
    public $groups = [];
    /** @var string[] */
    public $combinations = [];

    /**
     * @param array{
     *     groups?: array<int, string>,
     *     combinations?: array<int, string>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->groups = $data['groups'] ?? [];
        $obj->combinations = $data['combinations'] ?? [];

        return $obj;
    }
}
