<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;
use PrestaShop\Module\AutoUpgrade\PrestashopConfiguration;
use PrestaShop\Module\AutoUpgrade\Services\PhpVersionResolverService;
use PrestaShop\Module\AutoUpgrade\Upgrader;
use PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleCompatibilityChecker;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use PrestaShop\Module\AutoUpgrade\Xml\ChecksumCompare;

class UpgradeSelfCheckTest extends TestCase
{
    /**
     * When the shop is already up-to-date, getDestinationVersion() returns null.
     * getWarnings() must not throw a TypeError by passing null to ModuleCompatibilityChecker.
     */
    public function testGetWarningsDoesNotCrashWhenShopIsUpToDate(): void
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }

        $upgrader = $this->createMock(Upgrader::class);
        $upgrader->method('getDestinationVersion')->willReturn(null);
        $upgrader->method('getLatestCompatibleModuleVersion')->willReturn('');

        $updateConfiguration = $this->createMock(UpdateConfiguration::class);
        $updateConfiguration->method('isChannelLocal')->willReturn(false);

        $prestashopConfiguration = $this->createMock(PrestashopConfiguration::class);
        $translator = $this->createMock(Translator::class);
        $phpVersionResolverService = $this->createMock(PhpVersionResolverService::class);

        $checksumCompare = $this->createMock(ChecksumCompare::class);
        // Return false so isAlteredFilesNull() returns true, simplifying getWarnings()
        $checksumCompare->method('getTamperedFilesOnShop')->willReturn(false);

        $moduleAdapter = $this->createMock(ModuleAdapter::class);

        $moduleCompatibilityChecker = $this->createMock(ModuleCompatibilityChecker::class);
        // This must NOT be called when destinationVersion is null
        $moduleCompatibilityChecker->expects($this->never())->method('getModulesRequiringAttention');

        $selfCheck = new UpgradeSelfCheck(
            $upgrader,
            $updateConfiguration,
            $prestashopConfiguration,
            $translator,
            $phpVersionResolverService,
            $checksumCompare,
            $moduleAdapter,
            $moduleCompatibilityChecker,
            '/var/www/html',
            '/var/www/html/admin-dev',
            '/var/www/html/modules/autoupgrade',
            '9.2.0'
        );

        $warnings = $selfCheck->getWarnings();

        $this->assertArrayNotHasKey(UpgradeSelfCheck::MODULES_REQUIRE_ATTENTION, $warnings);
    }

    /**
     * checkModuleRequiresAttention() must be called in all cases except when the local archive
     * targets a non-officially-released version (PHP compatibility unknown).
     *
     * @dataProvider provideChannelConfigurationsExpectingModuleCheck
     */
    public function testCheckModuleRequiresAttentionIsCalled(bool $isChannelLocal, int $phpCompatibilityState): void
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }

        $upgrader = $this->createMock(Upgrader::class);
        $upgrader->method('getDestinationVersion')->willReturn('9.0.0');
        // Empty string triggers DESTINATION_VERSION_IS_NOT_SUPPORTED, skipping isModuleVersionLatest() call
        $upgrader->method('getLatestCompatibleModuleVersion')->willReturn('');

        $updateConfiguration = $this->createMock(UpdateConfiguration::class);
        $updateConfiguration->method('isChannelLocal')->willReturn($isChannelLocal);

        $prestashopConfiguration = $this->createMock(PrestashopConfiguration::class);
        $translator = $this->createMock(Translator::class);

        $phpVersionResolverService = $this->createMock(PhpVersionResolverService::class);
        $phpVersionResolverService->method('getPhpRequirementsState')->willReturn($phpCompatibilityState);

        $checksumCompare = $this->createMock(ChecksumCompare::class);
        $checksumCompare->method('getTamperedFilesOnShop')->willReturn(false);

        $moduleAdapter = $this->createMock(ModuleAdapter::class);
        $moduleAdapter->method('listModulesPresentInFolderAndInstalled')->willReturn([]);

        $moduleCompatibilityChecker = $this->createMock(ModuleCompatibilityChecker::class);
        $moduleCompatibilityChecker->expects($this->once())
            ->method('getModulesRequiringAttention')
            ->willReturn(['incompatible_modules' => [], 'uncertain_modules' => [], 'compatibility' => []]);

        $selfCheck = new UpgradeSelfCheck(
            $upgrader,
            $updateConfiguration,
            $prestashopConfiguration,
            $translator,
            $phpVersionResolverService,
            $checksumCompare,
            $moduleAdapter,
            $moduleCompatibilityChecker,
            '/var/www/html',
            '/var/www/html/admin-dev',
            '/var/www/html/modules/autoupgrade',
            '9.2.0'
        );

        $selfCheck->getWarnings();
    }

    /**
     * @return array<string, array{bool, int}>
     */
    public function provideChannelConfigurationsExpectingModuleCheck(): array
    {
        return [
            'non-local channel' => [false, PhpVersionResolverService::COMPATIBILITY_VALID],
            'local channel, officially released version (PHP valid)' => [true, PhpVersionResolverService::COMPATIBILITY_VALID],
            'local channel, officially released version (PHP invalid)' => [true, PhpVersionResolverService::COMPATIBILITY_INVALID],
        ];
    }

    /**
     * When the local archive targets a non-officially-released version, PHP compatibility cannot
     * be determined (COMPATIBILITY_UNKNOWN). In this case, module compatibility data is unavailable
     * too, so checkModuleRequiresAttention() must not be called.
     */
    public function testCheckModuleRequiresAttentionIsNotCalledForUnofficialLocalRelease(): void
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }

        $upgrader = $this->createMock(Upgrader::class);
        $upgrader->method('getDestinationVersion')->willReturn('9.0.0');
        $upgrader->method('getLatestCompatibleModuleVersion')->willReturn('');

        $updateConfiguration = $this->createMock(UpdateConfiguration::class);
        $updateConfiguration->method('isChannelLocal')->willReturn(true);

        $prestashopConfiguration = $this->createMock(PrestashopConfiguration::class);
        $translator = $this->createMock(Translator::class);

        $phpVersionResolverService = $this->createMock(PhpVersionResolverService::class);
        $phpVersionResolverService->method('getPhpRequirementsState')
            ->willReturn(PhpVersionResolverService::COMPATIBILITY_UNKNOWN);

        $checksumCompare = $this->createMock(ChecksumCompare::class);
        $checksumCompare->method('getTamperedFilesOnShop')->willReturn(false);

        $moduleAdapter = $this->createMock(ModuleAdapter::class);

        $moduleCompatibilityChecker = $this->createMock(ModuleCompatibilityChecker::class);
        $moduleCompatibilityChecker->expects($this->never())->method('getModulesRequiringAttention');

        $selfCheck = new UpgradeSelfCheck(
            $upgrader,
            $updateConfiguration,
            $prestashopConfiguration,
            $translator,
            $phpVersionResolverService,
            $checksumCompare,
            $moduleAdapter,
            $moduleCompatibilityChecker,
            '/var/www/html',
            '/var/www/html/admin-dev',
            '/var/www/html/modules/autoupgrade',
            '9.2.0'
        );

        $warnings = $selfCheck->getWarnings();

        $this->assertArrayNotHasKey(UpgradeSelfCheck::MODULES_REQUIRE_ATTENTION, $warnings);
    }
}
