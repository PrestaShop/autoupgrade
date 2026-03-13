<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
