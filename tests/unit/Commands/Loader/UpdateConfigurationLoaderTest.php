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

namespace PrestaShop\Module\AutoUpgrade\Tests\Unit\Commands\Loader;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Parameters\ConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\Loader\UpdateConfigurationLoader;
use PrestaShop\Module\AutoUpgrade\Parameters\PrestaShopConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;
use PrestaShop\Module\AutoUpgrade\Services\PrestashopVersionService;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\Validation\LocalChannelConfigurationValidator;
use PrestaShop\Module\AutoUpgrade\Validation\UpdateConfigurationValidator;

class UpdateConfigurationLoaderTest extends TestCase
{
    /** @var UpgradeContainer|PHPUnit_Framework_MockObject_MockObject */
    private $upgradeContainerMock;

    /** @var Logger|PHPUnit_Framework_MockObject_MockObject */
    private $loggerMock;

    /** @var UpdateConfigurationLoader */
    private $updateConfigurationLoader;

    protected function setUp()
    {
        parent::setUp();

        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }

        $this->upgradeContainerMock = $this->getMockBuilder(UpgradeContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->upgradeContainerMock->method('getLogger')
            ->willReturn($this->loggerMock);

        $translatorMock = $this->createMock(\PrestaShop\Module\AutoUpgrade\Adapter\Translator::class);
        $translatorMock->method('trans')->willReturnCallback(function ($id) {
            return $id;
        });

        $this->upgradeContainerMock->method('getTranslator')->willReturn($translatorMock);

        $this->updateConfigurationLoader = new UpdateConfigurationLoader($this->upgradeContainerMock);
    }

    public function testInitializeWithMissingConfig()
    {
        $updateConfigurationMock = $this->getMockBuilder(UpdateConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $updateConfigurationMock->expects($this->once())
            ->method('hasAllTheShopConfiguration')
            ->willReturn(false);

        $this->upgradeContainerMock->expects($this->once())
            ->method('getUpdateConfiguration')
            ->willReturn($updateConfigurationMock);

        $this->upgradeContainerMock->expects($this->once())
            ->method('initPrestaShopCore');

        $prestaShopConfigurationMock = $this->getMockBuilder(PrestaShopConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $prestaShopConfigurationMock->expects($this->once())
            ->method('fillInUpdateConfiguration')
            ->with($updateConfigurationMock);

        $this->upgradeContainerMock->expects($this->once())
            ->method('getPrestaShopConfiguration')
            ->willReturn($prestaShopConfigurationMock);

        $configurationStorageMock = $this->getMockBuilder(ConfigurationStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurationStorageMock->expects($this->once())
            ->method('save')
            ->with($updateConfigurationMock);

        $this->upgradeContainerMock->expects($this->once())
            ->method('getConfigurationStorage')
            ->willReturn($configurationStorageMock);

        $this->updateConfigurationLoader->initialize();
    }

    public function testInitializeWithCompleteConfig()
    {
        $updateConfigurationMock = $this->getMockBuilder(UpdateConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $updateConfigurationMock->expects($this->once())
            ->method('hasAllTheShopConfiguration')
            ->willReturn(true);

        $this->upgradeContainerMock->expects($this->once())
            ->method('getUpdateConfiguration')
            ->willReturn($updateConfigurationMock);

        $this->upgradeContainerMock->expects($this->never())
            ->method('initPrestaShopCore');

        $configurationStorageMock = $this->getMockBuilder(ConfigurationStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurationStorageMock->expects($this->once())
            ->method('save')
            ->with($updateConfigurationMock);

        $this->upgradeContainerMock->expects($this->once())
            ->method('getConfigurationStorage')
            ->willReturn($configurationStorageMock);

        $this->updateConfigurationLoader->initialize();
    }

    public function testLoadWithInvalidKeys()
    {
        // setup basic validation
        $updateConfigurationValidatorMock = $this->getMockBuilder(UpdateConfigurationValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $updateConfigurationValidatorMock->method('validate')->willReturn([]);
        $this->upgradeContainerMock->method('getUpdateConfigurationValidator')->willReturn($updateConfigurationValidatorMock);

        $updateConfigurationMock = $this->getMockBuilder(UpdateConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->upgradeContainerMock->method('getUpdateConfiguration')->willReturn($updateConfigurationMock);

        $configurationStorageMock = $this->getMockBuilder(ConfigurationStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->upgradeContainerMock->method('getConfigurationStorage')->willReturn($configurationStorageMock);

        $inputOptions = [
            'invalid_key' => 'value',
            UpdateConfiguration::CHANNEL => UpdateConfiguration::CHANNEL_MINOR, // A valid key
        ];

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with("Unknown configuration key 'invalid_key', Ignoring.");

        $exitCode = $this->updateConfigurationLoader->load($inputOptions);

        $this->assertSame(ExitCode::SUCCESS, $exitCode);
    }

    public function testLoadWithLocalChannelAndValidArchive()
    {
        $inputOptions = [
            UpdateConfiguration::ARCHIVE_ZIP => 'prestashop_8.1.0.zip',
        ];

        // Mocks for validation
        $updateConfigurationValidatorMock = $this->getMockBuilder(UpdateConfigurationValidator::class)->disableOriginalConstructor()->getMock();
        $updateConfigurationValidatorMock->method('validate')->willReturn([]);
        $this->upgradeContainerMock->method('getUpdateConfigurationValidator')->willReturn($updateConfigurationValidatorMock);

        $localChannelValidatorMock = $this->getMockBuilder(LocalChannelConfigurationValidator::class)->disableOriginalConstructor()->getMock();
        $localChannelValidatorMock->method('validate')->willReturn([]);
        $this->upgradeContainerMock->method('getLocalChannelConfigurationValidator')->willReturn($localChannelValidatorMock);

        // Mock getProperty for DL path
        $this->upgradeContainerMock->method('getProperty')
            ->with(UpgradeContainer::DOWNLOAD_PATH)
            ->willReturn('/tmp/downloads');

        // Mock version extraction
        $prestashopVersionServiceMock = $this->getMockBuilder(PrestashopVersionService::class)->disableOriginalConstructor()->getMock();
        $prestashopVersionServiceMock->expects($this->once())
            ->method('extractPrestashopVersionFromZip')
            ->with('/tmp/downloads/prestashop_8.1.0.zip')
            ->willReturn('8.1.0');
        $this->upgradeContainerMock->method('getPrestashopVersionService')->willReturn($prestashopVersionServiceMock);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('Update process will use archive.');

        $updateConfigurationMock = $this->getMockBuilder(UpdateConfiguration::class)->disableOriginalConstructor()->getMock();
        $this->upgradeContainerMock->method('getUpdateConfiguration')->willReturn($updateConfigurationMock);
        $configurationStorageMock = $this->getMockBuilder(ConfigurationStorage::class)->disableOriginalConstructor()->getMock();
        $this->upgradeContainerMock->method('getConfigurationStorage')->willReturn($configurationStorageMock);

        $exitCode = $this->updateConfigurationLoader->load($inputOptions);

        $this->assertSame(ExitCode::SUCCESS, $exitCode);
    }

    public function testLoadWithLocalChannelAndInvalidArchive()
    {
        $inputOptions = [
            UpdateConfiguration::ARCHIVE_ZIP => 'invalid.zip',
            UpdateConfiguration::CHANNEL => UpdateConfiguration::CHANNEL_LOCAL,
        ];

        // Mocks for validation
        $updateConfigurationValidatorMock = $this->getMockBuilder(UpdateConfigurationValidator::class)->disableOriginalConstructor()->getMock();
        $updateConfigurationValidatorMock->method('validate')->willReturn([]);
        $this->upgradeContainerMock->method('getUpdateConfigurationValidator')->willReturn($updateConfigurationValidatorMock);

        $localChannelValidatorMock = $this->getMockBuilder(LocalChannelConfigurationValidator::class)->disableOriginalConstructor()->getMock();
        $localChannelValidatorMock->method('validate')->willReturn([]);
        $this->upgradeContainerMock->method('getLocalChannelConfigurationValidator')->willReturn($localChannelValidatorMock);

        // Mock getProperty for DL path
        $this->upgradeContainerMock->method('getProperty')
            ->with(UpgradeContainer::DOWNLOAD_PATH)
            ->willReturn('/tmp/downloads');

        // Mock version extraction failure
        $prestashopVersionServiceMock = $this->getMockBuilder(PrestashopVersionService::class)->disableOriginalConstructor()->getMock();
        $prestashopVersionServiceMock->expects($this->once())
            ->method('extractPrestashopVersionFromZip')
            ->with('/tmp/downloads/invalid.zip')
            ->willThrowException(new \Exception('Bad zip'));

        $this->upgradeContainerMock->method('getPrestashopVersionService')->willReturn($prestashopVersionServiceMock);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('We couldn\'t find a PrestaShop version in the .zip file that was uploaded in your local archive. Please try again.');

        $exitCode = $this->updateConfigurationLoader->load($inputOptions);

        $this->assertSame(ExitCode::FAIL, $exitCode);
        $this->assertTrue($this->updateConfigurationLoader->hasError());
    }

    public function testLoadValidationError()
    {
        $inputOptions = [
            UpdateConfiguration::CHANNEL => 'something_invalid',
        ];

        $updateConfigurationValidatorMock = $this->getMockBuilder(UpdateConfigurationValidator::class)->disableOriginalConstructor()->getMock();
        $updateConfigurationValidatorMock->expects($this->once())->method('validate')->willReturn([
            ['message' => 'Invalid channel selected.'],
        ]);
        $this->upgradeContainerMock->method('getUpdateConfigurationValidator')->willReturn($updateConfigurationValidatorMock);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Invalid channel selected.');

        $exitCode = $this->updateConfigurationLoader->load($inputOptions);

        $this->assertSame(ExitCode::FAIL, $exitCode);
        $this->assertTrue($this->updateConfigurationLoader->hasError());
    }
}
