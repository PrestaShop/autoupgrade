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

class ModuleCompatibilityCheckerTest extends TestCase
{
    // These modules are known as native modules. No need to check the Marketplace -> OK
    const NATIVE_MODULES = ['statsdata', 'autoupgrade', 'psgdpr'];
    // Not existing in both APIs -> Uncertain
    const NON_EXISTING_MODULES = ['gamification'];
    // Found but no available version -> Incompatible
    const INCOMPATIBLE_MODULES = ['ps_mcp', 'ps_welcome'];
    // The rest -> OK

    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $distributionApiService;
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $marketplaceService;
    /** @var ModuleCompatibilityChecker */
    private $moduleCompatibilityChecker;

    protected function setUp()
    {
        parent::setUp();

        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }

        $this->distributionApiService = $this->mockDistributionApiService();
        $this->marketplaceService = $this->mockMarketplaceService();
        $this->moduleCompatibilityChecker = new ModuleCompatibilityChecker($this->distributionApiService, $this->marketplaceService);
    }

    public function testFullCheckup(): void
    {
        $installedModules = [
            ['name' => 'statsdata', 'currentVersion' => '1.0.0'],
            ['name' => 'autoupgrade', 'currentVersion' => '7.6.0'],
            ['name' => 'ps_welcome', 'currentVersion' => '2.0.0'],
            ['name' => 'psgdpr', 'currentVersion' => '4.1.0'],
            ['name' => 'gamification', 'currentVersion' => '1.2.3'],
            ['name' => 'ps_mcp', 'currentVersion' => '3.4.4'],
        ];

        /** @var array{incompatible_modules: string[], uncertain_modules: string[], compatibility: array<string, ?ModuleUpgradeCompatibility>} */
        $expected = [
            'incompatible_modules' => ['ps_welcome', 'ps_mcp'],
            'uncertain_modules' => ['gamification'],
            'compatibility' => [
                // Not checked
            ],
        ];

        $this->marketplaceService->expects($this->exactly(3))->method('getModuleDetail');

        $actual = $this->moduleCompatibilityChecker->getModulesRequiringAttention($installedModules, '99.99.99', null, ModuleCompatibilityChecker::COMPLETE_SEARCH);

        $this->assertEquals($expected['incompatible_modules'], $actual['incompatible_modules']);
        $this->assertEquals($expected['uncertain_modules'], $actual['uncertain_modules']);
    }

    // Detailed Checkup allows the marketplace to be displayed for all modules.
    public function testDetailedCheckup(): void
    {
        $installedModules = [
            ['name' => 'statsdata', 'currentVersion' => '1.0.0'],
            ['name' => 'autoupgrade', 'currentVersion' => '7.6.0'],
            ['name' => 'ps_welcome', 'currentVersion' => '2.0.0'],
            ['name' => 'psgdpr', 'currentVersion' => '4.1.0'],
            ['name' => 'gamification', 'currentVersion' => '1.2.3'],
            ['name' => 'ps_mcp', 'currentVersion' => '3.4.4'],
        ];

        /** @var array{incompatible_modules: string[], uncertain_modules: string[], compatibility: array<string, ?ModuleUpgradeCompatibility>} */
        $expected = [
            'incompatible_modules' => ['ps_welcome', 'ps_mcp'],
            'uncertain_modules' => ['gamification'],
            'compatibility' => [
                // Not checked
            ],
        ];

        // The results are the same but the number of calls to the marketplace is different
        $this->marketplaceService->expects($this->exactly(6))->method('getModuleDetail');

        $actual = $this->moduleCompatibilityChecker->getModulesRequiringAttention($installedModules, '99.99.99', null, ModuleCompatibilityChecker::DETAILED_SEARCH);

        $this->assertEquals($expected['incompatible_modules'], $actual['incompatible_modules']);
        $this->assertEquals($expected['uncertain_modules'], $actual['uncertain_modules']);
    }

    public function testWithOnlyNativeModules(): void
    {
        $installedModules = [
            ['name' => 'statsdata', 'currentVersion' => '1.0.0'],
            ['name' => 'autoupgrade', 'currentVersion' => '7.6.0'],
            ['name' => 'psgdpr', 'currentVersion' => '4.1.0'],
        ];

        /** @var array{incompatible_modules: string[], uncertain_modules: string[], compatibility: array<string, ?ModuleUpgradeCompatibility>} */
        $expected = [
            'incompatible_modules' => [],
            'uncertain_modules' => [],
            'compatibility' => [
                // Not checked
            ],
        ];

        $this->marketplaceService->expects($this->never())->method('getModuleDetail');

        $actual = $this->moduleCompatibilityChecker->getModulesRequiringAttention($installedModules, '99.99.99', null, ModuleCompatibilityChecker::COMPLETE_SEARCH);

        $this->assertEquals($expected['incompatible_modules'], $actual['incompatible_modules']);
        $this->assertEquals($expected['uncertain_modules'], $actual['uncertain_modules']);
    }

    public function testMethodReturnsQuicklyAfterFirstIncompatibleModules(): void
    {
        $installedModules = [
            ['name' => 'statsdata', 'currentVersion' => '1.0.0'],
            ['name' => 'autoupgrade', 'currentVersion' => '7.6.0'],
            ['name' => 'ps_welcome', 'currentVersion' => '2.0.0'],
            ['name' => 'psgdpr', 'currentVersion' => '4.1.0'],
            ['name' => 'gamification', 'currentVersion' => '1.2.3'],
            ['name' => 'ps_mcp', 'currentVersion' => '3.4.4'],
        ];

        /** @var array{incompatible_modules: string[], uncertain_modules: string[], compatibility: array<string, ?ModuleUpgradeCompatibility>} */
        $expected = [
            'incompatible_modules' => ['ps_welcome'],
            'uncertain_modules' => [],
            'compatibility' => [
                // Not checked
            ],
        ];

        $this->marketplaceService->expects($this->once())->method('getModuleDetail');

        $actual = $this->moduleCompatibilityChecker->getModulesRequiringAttention($installedModules, '99.99.99', null, ModuleCompatibilityChecker::QUICK_SEARCH);

        $this->assertEquals($expected['incompatible_modules'], $actual['incompatible_modules']);
        $this->assertEquals($expected['uncertain_modules'], $actual['uncertain_modules']);
    }

    public function testMethodReturnsQuicklyAfterFirstUncertainModule(): void
    {
        $installedModules = [
            ['name' => 'statsdata', 'currentVersion' => '1.0.0'],
            ['name' => 'autoupgrade', 'currentVersion' => '7.6.0'],
            ['name' => 'psgdpr', 'currentVersion' => '4.1.0'],
            ['name' => 'gamification', 'currentVersion' => '1.2.3'],
            ['name' => 'ps_mcp', 'currentVersion' => '3.4.4'],
        ];

        /** @var array{incompatible_modules: string[], uncertain_modules: string[], compatibility: array<string, ?ModuleUpgradeCompatibility>} */
        $expected = [
            'incompatible_modules' => [],
            'uncertain_modules' => ['gamification'],
            'compatibility' => [
                // Not checked
            ],
        ];

        $this->marketplaceService->expects($this->once())->method('getModuleDetail');

        $actual = $this->moduleCompatibilityChecker->getModulesRequiringAttention($installedModules, '99.99.99', null, ModuleCompatibilityChecker::QUICK_SEARCH);

        $this->assertEquals($expected['incompatible_modules'], $actual['incompatible_modules']);
        $this->assertEquals($expected['uncertain_modules'], $actual['uncertain_modules']);
    }

    public function testModuleWithNoReleaseNoMarketplaceIsUncertain()
    {
        $installedModules = [
            ['name' => 'wololo', 'currentVersion' => '1.0.0'],
        ];

        /** @var array{incompatible_modules: string[], uncertain_modules: string[], compatibility: array<string, ?ModuleUpgradeCompatibility>} */
        $expected = [
            'incompatible_modules' => [],
            'uncertain_modules' => ['wololo'],
            'compatibility' => [
                // Not checked
            ],
        ];

        $marketplaceService = $this->createMock(MarketplaceService::class);
        $marketplaceService->expects($this->once())->method('getModuleDetail')->willReturn(MarketplaceModule::fromArray(['product' => ['id_product' => 0]]));
        $marketplaceService->method('findCompatibleModuleUpgrade')->will($this->returnCallback(function () {
            $moduleUpgradeCompatibility = $this->createMock(ModuleUpgradeCompatibility::class);
            $moduleUpgradeCompatibility->method('getLatestRelease')->willReturn(null);

            return $moduleUpgradeCompatibility;
        }));
        $checker = new ModuleCompatibilityChecker($this->distributionApiService, $marketplaceService);

        $actual = $checker->getModulesRequiringAttention($installedModules, '99.99.99', null, ModuleCompatibilityChecker::COMPLETE_SEARCH);

        $this->assertEquals($expected['incompatible_modules'], $actual['incompatible_modules']);
        $this->assertEquals($expected['uncertain_modules'], $actual['uncertain_modules']);
    }

    public function testOfflineModuleNotCompatibleWithSourceVersionIsUncertain(): void
    {
        // iqitpopup only has releases for PS 1.6, so it was not compatible with the 8.x source version
        $installedModules = [
            ['name' => 'iqitpopup', 'currentVersion' => '1.0.0'],
        ];

        $marketplaceService = $this->createMock(MarketplaceService::class);
        $marketplaceService->method('getModuleDetail')->willReturn(
            MarketplaceModule::fromArray(['product' => ['is_active' => false]])
        );
        $marketplaceService->method('findCompatibleModuleUpgrade')->will($this->returnCallback(function () {
            $compat = $this->createMock(ModuleUpgradeCompatibility::class);
            $compat->method('isCompatible')->willReturn(false);
            $compat->method('getLatestRelease')->willReturn(Release::fromArray($this->createModuleRelease()));

            return $compat;
        }));

        $checker = new ModuleCompatibilityChecker($this->distributionApiService, $marketplaceService);
        $actual = $checker->getModulesRequiringAttention($installedModules, '9.0.0', '8.2.0', ModuleCompatibilityChecker::COMPLETE_SEARCH);

        $this->assertEmpty($actual['incompatible_modules']);
        $this->assertEquals(['iqitpopup'], $actual['uncertain_modules']);
    }

    public function testOfflineModuleCompatibleWithSourceVersionIsIncompatible(): void
    {
        // ps_edition_basic had releases for PS 8.x, so it was compatible with the source version
        $installedModules = [
            ['name' => 'ps_edition_basic', 'currentVersion' => '1.0.0'],
        ];

        $marketplaceService = $this->createMock(MarketplaceService::class);
        $marketplaceService->method('getModuleDetail')->willReturn(
            MarketplaceModule::fromArray(['product' => ['is_active' => false]])
        );
        // First call: target 9.0.0 → not compatible. Second call: source 8.2.0 → compatible.
        $marketplaceService->method('findCompatibleModuleUpgrade')->will($this->returnCallback(function (MarketplaceModule $module, string $version) {
            $compat = $this->createMock(ModuleUpgradeCompatibility::class);
            $compat->method('isCompatible')->willReturn($version === '8.2.0');
            $compat->method('getLatestRelease')->willReturn(Release::fromArray($this->createModuleRelease()));

            return $compat;
        }));

        $checker = new ModuleCompatibilityChecker($this->distributionApiService, $marketplaceService);
        $actual = $checker->getModulesRequiringAttention($installedModules, '9.0.0', '8.2.0', ModuleCompatibilityChecker::COMPLETE_SEARCH);

        $this->assertEquals(['ps_edition_basic'], $actual['incompatible_modules']);
        $this->assertEmpty($actual['uncertain_modules']);
    }

    public function testCompatibleModuleWithOfflinePageRemainsCompatible(): void
    {
        $installedModules = [
            ['name' => 'psxdesign', 'currentVersion' => '2.0.0'],
        ];

        $marketplaceService = $this->createMock(MarketplaceService::class);
        $marketplaceService->method('getModuleDetail')->willReturn(
            MarketplaceModule::fromArray(['product' => ['is_active' => false]])
        );
        $marketplaceService->method('findCompatibleModuleUpgrade')->will($this->returnCallback(function () {
            $compat = $this->createMock(ModuleUpgradeCompatibility::class);
            $compat->method('isCompatible')->willReturn(true);
            $compat->method('getLatestRelease')->willReturn(Release::fromArray($this->createModuleRelease()));

            return $compat;
        }));

        $checker = new ModuleCompatibilityChecker($this->distributionApiService, $marketplaceService);
        $actual = $checker->getModulesRequiringAttention($installedModules, '9.0.0', ModuleCompatibilityChecker::COMPLETE_SEARCH);

        $this->assertEmpty($actual['incompatible_modules']);
        $this->assertEmpty($actual['uncertain_modules']);
    }

    private function mockDistributionApiService()
    {
        $distributionApiService = $this->createMock(DistributionApiService::class);

        $nativeModules = [];
        foreach (self::NATIVE_MODULES as $name) {
            $nativeModules[] = new DistributionApiModule($name, '9.9.9', 'https://example.com/download', 'icon.png', null, null, null, null, null, null);
        }

        $distributionApiService->method('getModules')->willReturn($nativeModules);

        return $distributionApiService;
    }

    private function mockMarketplaceService()
    {
        $marketplaceService = $this->createMock(MarketplaceService::class);
        $marketplaceService->method('getModuleDetail')->will($this->returnCallback(function ($arg) {
            if (in_array($arg, self::NON_EXISTING_MODULES)) {
                throw new MarketplaceApiException('HTTP 404 Error: Module Unknown');
            }

            // Module exists. Compatibility will be reported with the other method
            // We hack one data of the module class as the technical name is not present.
            return MarketplaceModule::fromArray(['product' => [
                'id_product' => (int) !in_array($arg, self::INCOMPATIBLE_MODULES),
                'is_active' => true,
            ]]);
        }));

        $marketplaceService->method('findCompatibleModuleUpgrade')->will($this->returnCallback(function (MarketplaceModule $arg) {
            $isCompatible = (bool) $arg->product->id;

            $moduleUpgradeCompatibility = $this->createMock(ModuleUpgradeCompatibility::class);
            $moduleUpgradeCompatibility->method('isCompatible')->willReturn($isCompatible);
            $moduleUpgradeCompatibility->method('getLatestRelease')->willReturn(Release::fromArray($this->createModuleRelease()));

            return $moduleUpgradeCompatibility;
        }));

        return $marketplaceService;
    }

    private function createModuleRelease(): array
    {
        return [
            'product_version' => '5.2.2',
            'compatibility_checked' => 0,
            'compatible_from' => '9.0.0',
            'compatible_to' => '100.1.0',
            'is_ps_account' => 0,
            'is_cloudsync' => 0,
            'is_billing' => 0,
            'is_mcp_compliant' => 0,
            'is_eaa_compliant' => 0,
            'translations' => ['EN', 'DE', 'ES', 'FR', 'IT', 'NL', 'PL', 'PT', 'RO', 'RU'],
            'release_date' => '2026-01-05',
            'rgpd_compliant' => 1,
            'is_security_update' => 0,
            'has_overrides' => null,
            'is_major_update' => 0,
            'change_logs' => ['Bug fixes'],
        ];
    }
}
