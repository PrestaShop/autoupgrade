<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace;

class Overrides
{
    /** @var bool */
    public $complianceChecked = false;

    /**
     * @param array{
     *     compliance_checked?: bool
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->complianceChecked = (bool) ($data['compliance_checked'] ?? false);

        return $obj;
    }
}
