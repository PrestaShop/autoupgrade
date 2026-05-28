<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace\Release;
use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use PrestaShop\Module\AutoUpgrade\Services\MarketplaceService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleCompatibilityChecker;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class MarketplaceAndModuleCompatibilityTest extends TestCase
{
    /**
     * Integration tests hitting the real Marketplace and Distribution APIs.
     * Requires network access.
     */
    public function testModulesWithOfflinePageAndNoCompatibleReleaseAreUncertain(): void
    {
        $modules = [
            // Compatible and active modules
            ['name' => 'ps_mcp_tools',      'currentVersion' => '1.0.0'],
            ['name' => 'paypal',            'currentVersion' => '1.0.0'],
            ['name' => 'chronopost',        'currentVersion' => '1.0.0'],

            // psxdesign has is_active=false but does have releases compatible with PS 9.0.0.
            // It should be reported as compatible (absent from both lists).
            ['name' => 'psxdesign',         'currentVersion' => '2.0.0'],

            // Third-party modules with is_active=false and no release compatible with the target
            // version should be reported as uncertain, not incompatible.
            ['name' => 'iqitpopup',         'currentVersion' => '1.0.0'],
            ['name' => 'monetico',          'currentVersion' => '1.0.0'],
            ['name' => 'lghidesubcat',      'currentVersion' => '1.0.0'],

            // Module not maintained anymore on marketplace
            ['name' => 'systempay',         'currentVersion' => '1.0.0'],

            // Modules known as incomaptible
            ['name' => 'ps_edition_basic',  'currentVersion' => '1.0.0'],
        ];

        $translator = new Translator('en');
        $checker = new ModuleCompatibilityChecker(
            new DistributionApiService($translator),
            new MarketplaceService($translator)
        );

        $result = $checker->getModulesRequiringAttention($modules, '9.0.0', '8.2.0', ModuleCompatibilityChecker::COMPLETE_SEARCH);

        $this->assertEquals(['ps_edition_basic'], $result['incompatible_modules']);
        $this->assertEquals(['iqitpopup', 'monetico', 'lghidesubcat', 'systempay'], $result['uncertain_modules']);
    }

    public function testModulesWithOfflinePageAndNoCompatibleReleaseAreUncertainOnSameMajorVersion(): void
    {
        $modules = [
            // Compatible and active modules
            ['name' => 'ps_mcp_tools',      'currentVersion' => '1.0.0'],
            ['name' => 'paypal',            'currentVersion' => '1.0.0'],
            ['name' => 'chronopost',        'currentVersion' => '1.0.0'],

            // Modules for PrestaShop 1.7. Updates from and to PS 8 tell if the modules has been made compliant.
            ['name' => 'anscrolltop',       'currentVersion' => '1.0.0'],
            ['name' => 'ps_buybuttonlite',  'currentVersion' => '1.0.0'],
            ['name' => 'psaddonsconnect',   'currentVersion' => '1.0.0'],
            ['name' => 'welcome',           'currentVersion' => '1.0.0'],
        ];

        $translator = new Translator('en');
        $checker = new ModuleCompatibilityChecker(
            new DistributionApiService($translator),
            new MarketplaceService($translator)
        );

        $result = $checker->getModulesRequiringAttention($modules, '8.2.5', '8.2.1', ModuleCompatibilityChecker::COMPLETE_SEARCH);

        $this->assertEquals([], $result['incompatible_modules']);
        $this->assertEquals(['anscrolltop', 'ps_buybuttonlite', 'psaddonsconnect', 'welcome'], $result['uncertain_modules']);
    }
}
