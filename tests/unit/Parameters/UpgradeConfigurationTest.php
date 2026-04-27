<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace Parameters;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;

class UpgradeConfigurationTest extends TestCase
{
    public function testUpdateConfigurationConfigIsFilledWithPrestaShopOne(): void
    {
        $updateConfiguration = new UpdateConfiguration();

        $this->assertFalse($updateConfiguration->hasAllTheShopConfiguration());

        // We can't use the class PrestaShopConfiguration directly because of its reliance on the Core.
        // Reproduce its alterations below:
        $updateConfiguration->merge([
            UpdateConfiguration::INSTALLED_LANGUAGES => ['fr', 'de', 'jp'],
        ]);

        $this->assertTrue($updateConfiguration->hasAllTheShopConfiguration());
    }
}
