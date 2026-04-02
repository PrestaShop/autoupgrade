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

namespace PrestaShop\Module\AutoUpgrade\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\PrestashopConfiguration;
use PrestaShop\Module\AutoUpgrade\Services\PhpVersionResolverService;
use PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleCompatibilityChecker;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use PrestaShop\Module\AutoUpgrade\Upgrader;
use PrestaShop\Module\AutoUpgrade\Xml\ChecksumCompare;

class UpgradeSelfCheckTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if (PHP_VERSION_ID >= 80100) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8.1+ prevents this test to run.');
        }
    }

    private function getUpgradeSelfCheckMock(array $methods, bool $channelLocal = false)
    {
        $upgrader = $this->createMock(Upgrader::class);
        $upgrader->method('getDestinationVersion')->willReturn('8.0.0');

        $updateConfiguration = $this->createMock(UpgradeConfiguration::class);
        $updateConfiguration->method('isChannelLocal')->willReturn($channelLocal);

        $prestashopConfiguration = $this->createMock(PrestashopConfiguration::class);
        $translator = $this->createMock(Translator::class);
        $phpRequirementService = $this->createMock(PhpVersionResolverService::class);
        $checksumCompare = $this->createMock(ChecksumCompare::class);
        $moduleAdapter = $this->createMock(ModuleAdapter::class);
        $moduleCompatibilityChecker = $this->createMock(ModuleCompatibilityChecker::class);

        return $this->getMockBuilder(UpgradeSelfCheck::class)
            ->setConstructorArgs([
                $upgrader,
                $updateConfiguration,
                $prestashopConfiguration,
                $translator,
                $phpRequirementService,
                $checksumCompare,
                $moduleAdapter,
                $moduleCompatibilityChecker,
                '/prod/root',
                '/admin',
                '/autoupgrade',
                '1.7.8.9'
            ])
            ->onlyMethods($methods)
            ->getMock();
    }

    public function testGetErrorsAllGood()
    {
        $mock = $this->getUpgradeSelfCheckMock([
            'isRootDirectoryWritable',
            'isAdminAutoUpgradeDirectoryWritable',
            'isFOpenOrCurlEnabled',
            'isZipEnabled',
            'isLocalEnvironment',
            'isShopDeactivated',
            'isCacheDisabled',
            'getMaxExecutionTime',
            'isApacheModRewriteEnabled',
            'getNotLoadedPhpExtensions',
            'getNotExistsPhpFunctions',
            'isMemoryLimitValid',
            'isPhpFileUploadsConfigurationEnabled',
            'checkKeyGeneration',
            'getNotWritingDirectories',
            'isShopVersionMatchingVersionInDatabase',
            'isAlteredFilesNull',
            'getPhpRequirementsState',
        ]);

        $mock->method('isRootDirectoryWritable')->willReturn(true);
        $mock->method('isAdminAutoUpgradeDirectoryWritable')->willReturn(true);
        $mock->method('isFOpenOrCurlEnabled')->willReturn(true);
        $mock->method('isZipEnabled')->willReturn(true);
        $mock->method('isLocalEnvironment')->willReturn(false);
        $mock->method('isShopDeactivated')->willReturn(true);
        $mock->method('isCacheDisabled')->willReturn(true);
        $mock->method('getMaxExecutionTime')->willReturn(30);
        $mock->method('isApacheModRewriteEnabled')->willReturn(true);
        $mock->method('getNotLoadedPhpExtensions')->willReturn([]);
        $mock->method('getNotExistsPhpFunctions')->willReturn([]);
        $mock->method('isMemoryLimitValid')->willReturn(true);
        $mock->method('isPhpFileUploadsConfigurationEnabled')->willReturn(true);
        $mock->method('checkKeyGeneration')->willReturn(true);
        $mock->method('getNotWritingDirectories')->willReturn([]);
        $mock->method('isShopVersionMatchingVersionInDatabase')->willReturn(true);
        $mock->method('isAlteredFilesNull')->willReturn(false);
        $mock->method('getPhpRequirementsState')->willReturn(PhpVersionResolverService::COMPATIBILITY_VALID);

        $errors = $mock->getErrors();
        $this->assertEmpty($errors);
    }

    public function testGetErrorsWithSomeErrors()
    {
        $mock = $this->getUpgradeSelfCheckMock([
            'isRootDirectoryWritable',
            'isAdminAutoUpgradeDirectoryWritable',
            'isFOpenOrCurlEnabled',
            'isZipEnabled',
            'isLocalEnvironment',
            'isShopDeactivated',
            'isCacheDisabled',
            'getMaxExecutionTime',
            'isApacheModRewriteEnabled',
            'getNotLoadedPhpExtensions',
            'getNotExistsPhpFunctions',
            'isMemoryLimitValid',
            'isPhpFileUploadsConfigurationEnabled',
            'checkKeyGeneration',
            'getNotWritingDirectories',
            'isShopVersionMatchingVersionInDatabase',
            'isAlteredFilesNull',
            'getPhpRequirementsState',
        ], true);

        $mock->method('isRootDirectoryWritable')->willReturn(false); // error 1
        $mock->method('isAdminAutoUpgradeDirectoryWritable')->willReturn(true);
        $mock->method('isFOpenOrCurlEnabled')->willReturn(true);
        $mock->method('isZipEnabled')->willReturn(false); // error 2
        $mock->method('isLocalEnvironment')->willReturn(false);
        $mock->method('isShopDeactivated')->willReturn(false); // error 3 (maintenance)
        $mock->method('isCacheDisabled')->willReturn(true);
        $mock->method('getMaxExecutionTime')->willReturn(15); // error 4
        $mock->method('isApacheModRewriteEnabled')->willReturn(true);
        $mock->method('getNotLoadedPhpExtensions')->willReturn([]);
        $mock->method('getNotExistsPhpFunctions')->willReturn(['fopen']); // error 5
        $mock->method('isMemoryLimitValid')->willReturn(true);
        $mock->method('isPhpFileUploadsConfigurationEnabled')->willReturn(true);
        $mock->method('checkKeyGeneration')->willReturn(true);
        $mock->method('getNotWritingDirectories')->willReturn(['/test']); // error 6
        $mock->method('isShopVersionMatchingVersionInDatabase')->willReturn(true);
        $mock->method('isAlteredFilesNull')->willReturn(true); // error 7
        $mock->method('getPhpRequirementsState')->willReturn(PhpVersionResolverService::COMPATIBILITY_INVALID); // error 8

        $errors = $mock->getErrors();
        $this->assertCount(8, $errors);
        $this->assertArrayHasKey(UpgradeSelfCheck::ROOT_DIRECTORY_NOT_WRITABLE, $errors);
        $this->assertArrayHasKey(UpgradeSelfCheck::ZIP_DISABLED, $errors);
        $this->assertArrayHasKey(UpgradeSelfCheck::MAINTENANCE_MODE_DISABLED, $errors);
        $this->assertArrayHasKey(UpgradeSelfCheck::MAX_EXECUTION_TIME_VALUE_INCORRECT, $errors);
        $this->assertArrayHasKey(UpgradeSelfCheck::NOT_EXIST_PHP_FUNCTIONS_LIST_NOT_EMPTY, $errors);
        $this->assertArrayHasKey(UpgradeSelfCheck::NOT_WRITING_DIRECTORY_LIST_NOT_EMPTY, $errors);
        $this->assertArrayHasKey(UpgradeSelfCheck::TEMPERED_FILES_UNKNOWN, $errors);
        $this->assertArrayHasKey(UpgradeSelfCheck::PHP_COMPATIBILITY_INVALID, $errors);
    }

    public function testGetWarningsAllGood()
    {
        $mock = $this->getUpgradeSelfCheckMock([
            'isAlteredFilesNull',
            'getCoreAlteredFiles',
            'getCoreMissingFiles',
            'getThemeAlteredFiles',
            'getThemeMissingFiles',
            'getLatestCompatibleModuleVersion',
            'isModuleVersionLatest',
            'getPhpRequirementsState',
            'checkModuleRequiresAttention'
        ]);

        $mock->method('isAlteredFilesNull')->willReturn(false);
        $mock->method('getCoreAlteredFiles')->willReturn([]);
        $mock->method('getCoreMissingFiles')->willReturn([]);
        $mock->method('getThemeAlteredFiles')->willReturn([]);
        $mock->method('getThemeMissingFiles')->willReturn([]);
        $mock->method('getLatestCompatibleModuleVersion')->willReturn('1.0.0');
        $mock->method('isModuleVersionLatest')->willReturn(true);
        $mock->method('getPhpRequirementsState')->willReturn(PhpVersionResolverService::COMPATIBILITY_VALID);
        $mock->method('checkModuleRequiresAttention')->willReturn(false);

        $warnings = $mock->getWarnings();
        $this->assertEmpty($warnings);
    }

    public function testGetWarningsWithSomeWarnings()
    {
        $mock = $this->getUpgradeSelfCheckMock([
            'isAlteredFilesNull',
            'getCoreAlteredFiles',
            'getCoreMissingFiles',
            'getThemeAlteredFiles',
            'getThemeMissingFiles',
            'getLatestCompatibleModuleVersion',
            'isModuleVersionLatest',
            'getPhpRequirementsState',
            'checkModuleRequiresAttention'
        ], true);

        $mock->method('isAlteredFilesNull')->willReturn(false);
        $mock->method('getCoreAlteredFiles')->willReturn(['file.php']); // warning 1
        $mock->method('getCoreMissingFiles')->willReturn([]);
        $mock->method('getThemeAlteredFiles')->willReturn([]);
        $mock->method('getThemeMissingFiles')->willReturn(['theme/file.tpl']); // warning 2
        $mock->method('getLatestCompatibleModuleVersion')->willReturn('2.0.0');
        $mock->method('isModuleVersionLatest')->willReturn(false); // warning 3
        $mock->method('getPhpRequirementsState')->willReturn(PhpVersionResolverService::COMPATIBILITY_UNKNOWN); // warning 4
        $mock->method('checkModuleRequiresAttention')->willReturn(true);

        $warnings = $mock->getWarnings();
        $this->assertCount(4, $warnings);
        $this->assertArrayHasKey(UpgradeSelfCheck::CORE_TEMPERED_FILES_LIST_NOT_EMPTY, $warnings);
        $this->assertArrayHasKey(UpgradeSelfCheck::THEME_TEMPERED_FILES_LIST_NOT_EMPTY, $warnings);
        $this->assertArrayHasKey(UpgradeSelfCheck::MODULE_VERSION_IS_OUT_OF_DATE, $warnings);
        $this->assertArrayHasKey(UpgradeSelfCheck::PHP_COMPATIBILITY_UNKNOWN, $warnings);
        $this->assertArrayNotHasKey(UpgradeSelfCheck::MODULES_REQUIRE_ATTENTION, $warnings);
    }

    public function testGetWarningsDestinationVersionNotSupported()
    {
        $mock = $this->getUpgradeSelfCheckMock([
            'isAlteredFilesNull',
            'getLatestCompatibleModuleVersion',
            'getPhpRequirementsState',
            'checkModuleRequiresAttention'
        ]);

        $mock->method('isAlteredFilesNull')->willReturn(true);
        $mock->method('getLatestCompatibleModuleVersion')->willReturn(''); // warning 1
        $mock->method('getPhpRequirementsState')->willReturn(PhpVersionResolverService::COMPATIBILITY_VALID);
        $mock->method('checkModuleRequiresAttention')->willReturn(false);

        $warnings = $mock->getWarnings();
        $this->assertArrayHasKey(UpgradeSelfCheck::DESTINATION_VERSION_IS_NOT_SUPPORTED, $warnings);
        $this->assertArrayNotHasKey(UpgradeSelfCheck::MODULE_VERSION_IS_OUT_OF_DATE, $warnings);
    }

    public function testGetWarningsModulesRequireAttention()
    {
        $mock = $this->getUpgradeSelfCheckMock([
            'isAlteredFilesNull',
            'getLatestCompatibleModuleVersion',
            'isModuleVersionLatest',
            'getPhpRequirementsState',
            'checkModuleRequiresAttention'
        ]);

        $mock->method('isAlteredFilesNull')->willReturn(true);
        $mock->method('getLatestCompatibleModuleVersion')->willReturn('1.0.0');
        $mock->method('isModuleVersionLatest')->willReturn(true);
        $mock->method('getPhpRequirementsState')->willReturn(PhpVersionResolverService::COMPATIBILITY_VALID);
        $mock->method('checkModuleRequiresAttention')->willReturn(true); // warning 1

        $warnings = $mock->getWarnings();
        $this->assertArrayHasKey(UpgradeSelfCheck::MODULES_REQUIRE_ATTENTION, $warnings);
    }
}
