<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Tests\Unit\Commands\Loader;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Parameters\Loader\RestoreConfigurationLoader;
use PrestaShop\Module\AutoUpgrade\Parameters\RestoreConfiguration;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\Validation\RestoreConfigurationValidator;

class RestoreConfigurationLoaderTest extends TestCase
{
    /** @var UpgradeContainer|PHPUnit_Framework_MockObject_MockObject */
    private $upgradeContainerMock;

    /** @var Logger|PHPUnit_Framework_MockObject_MockObject */
    private $loggerMock;

    /** @var RestoreConfigurationLoader */
    private $restoreConfigurationLoader;

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

        $this->restoreConfigurationLoader = new RestoreConfigurationLoader($this->upgradeContainerMock);
    }

    public function testLoadWithInvalidKeys()
    {
        // setup basic validation
        $restoreConfigurationValidatorMock = $this->getMockBuilder(RestoreConfigurationValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $restoreConfigurationValidatorMock->method('validate')->willReturn([]);
        $this->upgradeContainerMock->method('getRestoreConfigurationValidator')->willReturn($restoreConfigurationValidatorMock);

        $restoreConfigurationMock = $this->getMockBuilder(RestoreConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->upgradeContainerMock->method('getRestoreConfiguration')->willReturn($restoreConfigurationMock);

        $inputOptions = [
            'invalid_key' => 'value',
            RestoreConfiguration::KEEP_IMAGES => true, // A valid key
        ];

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with("Unknown configuration key 'invalid_key', Ignoring.");

        $exitCode = $this->restoreConfigurationLoader->load($inputOptions);

        $this->assertSame(ExitCode::SUCCESS, $exitCode);
    }

    public function testLoadValidationError()
    {
        $inputOptions = [
            RestoreConfiguration::KEEP_MAILS => 'invalid_type',
        ];

        $restoreConfigurationValidatorMock = $this->getMockBuilder(RestoreConfigurationValidator::class)->disableOriginalConstructor()->getMock();
        $restoreConfigurationValidatorMock->expects($this->once())->method('validate')->willReturn([
            ['message' => 'Invalid choice'],
        ]);
        $this->upgradeContainerMock->method('getRestoreConfigurationValidator')->willReturn($restoreConfigurationValidatorMock);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Invalid choice');

        $exitCode = $this->restoreConfigurationLoader->load($inputOptions);

        $this->assertSame(ExitCode::FAIL, $exitCode);
        $this->assertTrue($this->restoreConfigurationLoader->hasError());
    }

    public function testLoadValidConfig()
    {
        $inputOptions = [
            RestoreConfiguration::KEEP_IMAGES => true,
        ];

        $restoreConfigurationValidatorMock = $this->getMockBuilder(RestoreConfigurationValidator::class)->disableOriginalConstructor()->getMock();
        $restoreConfigurationValidatorMock->expects($this->once())->method('validate')->willReturn([]);
        $this->upgradeContainerMock->method('getRestoreConfigurationValidator')->willReturn($restoreConfigurationValidatorMock);

        $restoreConfigurationMock = $this->getMockBuilder(RestoreConfiguration::class)->disableOriginalConstructor()->getMock();
        $restoreConfigurationMock->expects($this->once())
            ->method('merge')
            ->with($inputOptions);

        $this->upgradeContainerMock->method('getRestoreConfiguration')->willReturn($restoreConfigurationMock);

        $configurationStorageMock = $this->getMockBuilder(\PrestaShop\Module\AutoUpgrade\Parameters\ConfigurationStorage::class)->disableOriginalConstructor()->getMock();
        $configurationStorageMock->expects($this->once())
            ->method('save')
            ->with($restoreConfigurationMock);

        $this->upgradeContainerMock->method('getConfigurationStorage')->willReturn($configurationStorageMock);

        $exitCode = $this->restoreConfigurationLoader->load($inputOptions);

        $this->assertSame(ExitCode::SUCCESS, $exitCode);
        $this->assertFalse($this->restoreConfigurationLoader->hasError());
    }
}
