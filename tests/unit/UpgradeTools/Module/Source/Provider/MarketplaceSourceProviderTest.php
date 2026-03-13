<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider\MarketplaceSourceProvider;
use PrestaShop\Module\AutoUpgrade\Xml\FileLoader;

class MarketplaceSourceProviderTest extends TestCase
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
        $fileLoader = $this->createMock(FileLoader::class);
        $fileLoader->method('getXmlFile')->willReturn(simplexml_load_file(__DIR__ . '/../../../../../fixtures/api-marketplace/native-modules-list.xml'));
        $fileConfigurationStorageMock = $this->createMock(FileStorage::class);

        $sourceProvider = new MarketplaceSourceProvider('9.0.0', 'C:\mocked', $fileLoader, $fileConfigurationStorageMock);

        $fileConfigurationStorageMock->expects($this->once())->method('exists');
        $fileConfigurationStorageMock->expects($this->once())->method('save');
        $fileConfigurationStorageMock->expects($this->never())->method('load');

        $results1 = $sourceProvider->getUpdatesOfModule('blockwishlist', '0.9.0');
        $results2 = $sourceProvider->getUpdatesOfModule('blockwishlist', '0.9.0');

        $this->assertEquals($results1, $results2);
        $this->assertEquals([
            new ModuleSource('blockwishlist', '3.0.1', 'https://api.addons.prestashop.com/?id_module=9131&method=module&version=9.0.0', true),
        ], $results2);
    }

    public function testCacheGenerationWithNoData()
    {
        $fileLoader = $this->createMock(FileLoader::class);
        $fileLoader->method('getXmlFile')->willReturn(simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><modules></modules>'));
        $fileConfigurationStorageMock = $this->createMock(FileStorage::class);

        $sourceProvider = new MarketplaceSourceProvider('9.0.0', 'C:\mocked', $fileLoader, $fileConfigurationStorageMock);

        $fileConfigurationStorageMock->expects($this->once())->method('exists');
        $fileConfigurationStorageMock->expects($this->once())->method('save');
        $fileConfigurationStorageMock->expects($this->never())->method('load');

        $sourceProvider->getUpdatesOfModule('test1', '1.0.0');
        $sourceProvider->getUpdatesOfModule('test2', '1.0.0');
    }

    public function testCacheLoading()
    {
        $fileLoader = $this->createMock(FileLoader::class);
        $fileConfigurationStorageMock = $this->createMock(FileStorage::class);
        $fileConfigurationStorageMock->method('exists')->willReturn(true);
        $fileConfigurationStorageMock->method('load')->willReturn([]);

        $sourceProvider = new MarketplaceSourceProvider('9.0.0', 'C:\mocked', $fileLoader, $fileConfigurationStorageMock);

        $fileConfigurationStorageMock->expects($this->once())->method('exists');
        $fileConfigurationStorageMock->expects($this->once())->method('load');
        $fileConfigurationStorageMock->expects($this->never())->method('save');

        $sourceProvider->getUpdatesOfModule('test1', '1.0.0');
        $sourceProvider->getUpdatesOfModule('test2', '1.0.0');
    }
}
