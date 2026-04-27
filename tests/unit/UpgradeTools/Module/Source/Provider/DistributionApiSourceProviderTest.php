<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider\DistributionApiSourceProvider;

class DistributionApiSourceProviderTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }
    }

    public function testCacheGenerationWithData()
    {
        $distributionApiService = $this->createPartialMock(DistributionApiService::class, ['getApiEndpoint']);
        $distributionApiService->method('getApiEndpoint')->willReturn([
            'autoupgrade' => [
                'version' => '5.0.0',
                'download_url' => 'https://example.com/autoupgrade.zip',
                'icon' => '',
            ],
            'ps_apiresources' => [
                'version' => '1.0.0',
                'download_url' => 'https://example.com/ps_apiresources.zip',
                'icon' => '',
            ],
        ]);
        $fileConfigurationStorageMock = $this->createMock(FileStorage::class);

        $sourceProvider = new DistributionApiSourceProvider('9.0.2', $distributionApiService, $fileConfigurationStorageMock);

        $fileConfigurationStorageMock->expects($this->once())->method('exists');
        $fileConfigurationStorageMock->expects($this->once())->method('save');
        $fileConfigurationStorageMock->expects($this->never())->method('load');

        $results1 = $sourceProvider->getUpdatesOfModule('ps_apiresources', '0.9.0');
        $results2 = $sourceProvider->getUpdatesOfModule('ps_apiresources', '0.9.0');

        $this->assertEquals($results1, $results2);
        $this->assertEquals([
            new ModuleSource('ps_apiresources', '1.0.0', 'https://example.com/ps_apiresources.zip', true),
        ], $results2);
    }

    public function testCacheGenerationWithNoData()
    {
        $distributionApiService = $this->createMock(DistributionApiService::class);
        $distributionApiService->method('getModules')->willReturn([]);
        $fileConfigurationStorageMock = $this->createMock(FileStorage::class);

        $sourceProvider = new DistributionApiSourceProvider('9.0.2', $distributionApiService, $fileConfigurationStorageMock);

        $fileConfigurationStorageMock->expects($this->once())->method('exists');
        $fileConfigurationStorageMock->expects($this->once())->method('save');
        $fileConfigurationStorageMock->expects($this->never())->method('load');

        $this->assertEmpty($sourceProvider->getUpdatesOfModule('test1', '1.0.0'));
    }

    public function testCacheLoading()
    {
        $distributionApiService = $this->createMock(DistributionApiService::class);
        $fileConfigurationStorageMock = $this->createMock(FileStorage::class);
        $fileConfigurationStorageMock->method('exists')->willReturn(true);
        $fileConfigurationStorageMock->method('load')->willReturn([]);

        $sourceProvider = new DistributionApiSourceProvider('9.0.2', $distributionApiService, $fileConfigurationStorageMock);

        $fileConfigurationStorageMock->expects($this->once())->method('exists');
        $fileConfigurationStorageMock->expects($this->once())->method('load');
        $fileConfigurationStorageMock->expects($this->never())->method('save');

        $sourceProvider->getUpdatesOfModule('test1', '1.0.0');
    }
}
