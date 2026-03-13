<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace;

class Compatibility
{
    /** @var bool */
    public $checked = false;
    /** @var string[] */
    public $versions = [];
    /** @var string[] */
    public $defaultVersions = [];

    /**
     * @param array{
     *     compliance_checked?: bool,
     *     versions?: array<int, string>,
     *     default_versions?: array<int, string>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $obj = new self();

        $obj->checked = (bool) ($data['compliance_checked'] ?? false);
        $obj->versions = $data['versions'] ?? [];
        $obj->defaultVersions = $data['default_versions'] ?? [];

        return $obj;
    }
}
