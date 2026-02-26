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
use PrestaShop\Module\AutoUpgrade\Parameters\BackupConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\Loader\BackupConfigurationLoader;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\Validation\BackupConfigurationValidator;

class BackupConfigurationLoaderTest extends TestCase
{
    /** @var UpgradeContainer|PHPUnit_Framework_MockObject_MockObject */
    private $upgradeContainerMock;

    /** @var Logger|PHPUnit_Framework_MockObject_MockObject */
    private $loggerMock;

    /** @var BackupConfigurationLoader */
    private $backupConfigurationLoader;

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

        $this->backupConfigurationLoader = new BackupConfigurationLoader($this->upgradeContainerMock);
    }

    public function testLoadWithInvalidKeys()
    {
        // setup basic validation
        $backupConfigurationValidatorMock = $this->getMockBuilder(BackupConfigurationValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $backupConfigurationValidatorMock->method('validate')->willReturn([]);
        $this->upgradeContainerMock->method('getBackupConfigurationValidator')->willReturn($backupConfigurationValidatorMock);

        $backupConfigurationMock = $this->getMockBuilder(BackupConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->upgradeContainerMock->method('getBackupConfiguration')->willReturn($backupConfigurationMock);

        $inputOptions = [
            'invalid_key' => 'value',
            BackupConfiguration::KEEP_IMAGES => true, // A valid key
        ];

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with("Unknown configuration key 'invalid_key', Ignoring.");

        $exitCode = $this->backupConfigurationLoader->load($inputOptions);

        $this->assertSame(ExitCode::SUCCESS, $exitCode);
    }

    public function testLoadValidationError()
    {
        $inputOptions = [
            BackupConfiguration::KEEP_IMAGES => 'invalid_type',
        ];

        $backupConfigurationValidatorMock = $this->getMockBuilder(BackupConfigurationValidator::class)->disableOriginalConstructor()->getMock();
        $backupConfigurationValidatorMock->expects($this->once())->method('validate')->willReturn([
            ['message' => 'Invalid choice'],
        ]);
        $this->upgradeContainerMock->method('getBackupConfigurationValidator')->willReturn($backupConfigurationValidatorMock);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Invalid choice');

        $exitCode = $this->backupConfigurationLoader->load($inputOptions);

        $this->assertSame(ExitCode::FAIL, $exitCode);
        $this->assertTrue($this->backupConfigurationLoader->hasError());
    }

    public function testLoadValidConfig()
    {
        $inputOptions = [
            BackupConfiguration::KEEP_IMAGES => true,
        ];

        $backupConfigurationValidatorMock = $this->getMockBuilder(BackupConfigurationValidator::class)->disableOriginalConstructor()->getMock();
        $backupConfigurationValidatorMock->expects($this->once())->method('validate')->willReturn([]);
        $this->upgradeContainerMock->method('getBackupConfigurationValidator')->willReturn($backupConfigurationValidatorMock);

        $backupConfigurationMock = $this->getMockBuilder(BackupConfiguration::class)->disableOriginalConstructor()->getMock();
        $backupConfigurationMock->expects($this->once())
            ->method('merge')
            ->with($inputOptions);

        $this->upgradeContainerMock->method('getBackupConfiguration')->willReturn($backupConfigurationMock);

        // writeConfig logic relies on getUpdateConfiguration inside AbstractConfigurationLoader
        // for simplicity, we mock getUpdateConfiguration if used or let it fall back.
        // Actually writeConfig uses:
        // $configuration = $this->container->{"get{$type}Configuration"}();
        // and $this->container->getConfigurationStorage()->save($configuration);
        // so we mock both:

        $configurationStorageMock = $this->getMockBuilder(\PrestaShop\Module\AutoUpgrade\Parameters\ConfigurationStorage::class)->disableOriginalConstructor()->getMock();
        $configurationStorageMock->expects($this->once())
            ->method('save')
            ->with($backupConfigurationMock);

        $this->upgradeContainerMock->method('getConfigurationStorage')->willReturn($configurationStorageMock);

        $exitCode = $this->backupConfigurationLoader->load($inputOptions);

        $this->assertSame(ExitCode::SUCCESS, $exitCode);
        $this->assertFalse($this->backupConfigurationLoader->hasError());
    }
}
