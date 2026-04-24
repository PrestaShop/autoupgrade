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

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Exceptions\MarketplaceApiException;
use PrestaShop\Module\AutoUpgrade\Models\Module\DistributionApi\Module as DistributionApiModule;
use PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace\Module as MarketplaceModule;
use PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace\ModuleUpgradeCompatibility;
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
}
