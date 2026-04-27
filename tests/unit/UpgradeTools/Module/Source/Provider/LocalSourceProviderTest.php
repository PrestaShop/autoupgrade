<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider\LocalSourceProvider;

class LocalSourceProviderTest extends TestCase
{
    public function testCacheGenerationWithData()
    {
        $fileConfigurationStorageMock = $this->createMock(FileStorage::class);

        $fixtureFolder = sys_get_temp_dir() . '/' . self::class;
        @mkdir($fixtureFolder);

        $zip = new ZipArchive();
        $zip->open($fixtureFolder . '/yoloupgrade.zip', ZipArchive::CREATE);
        $zip->addFromString('yoloupgrade/config.xml', '<?xml version="1.0" encoding="UTF-8" ?>
<module>
    <name>yoloupgrade</name>
    <displayName><![CDATA[Yolo-Click Upgrade]]></displayName>
    <version><![CDATA[1.0.0]]></version>
    <description><![CDATA[Upgrade to the latest version of PrestaShop in a few clicks, thanks to this automated method.]]></description>
    <author><![CDATA[PrestaShop]]></author>
    <tab><![CDATA[administration]]></tab>
    <is_configurable>1</is_configurable>
    <need_instance>1</need_instance>
</module>');
        $zip->close();

        $sourceProvider = new LocalSourceProvider($fixtureFolder, $fileConfigurationStorageMock);

        $fileConfigurationStorageMock->expects($this->once())->method('exists');
        $fileConfigurationStorageMock->expects($this->once())->method('save');
        $fileConfigurationStorageMock->expects($this->never())->method('load');

        $results1 = $sourceProvider->getUpdatesOfModule('yoloupgrade', '0.9.0');
        $results2 = $sourceProvider->getUpdatesOfModule('yoloupgrade', '0.9.0');

        $this->assertEquals($results1, $results2);
        $this->assertEquals([
            new ModuleSource('yoloupgrade', '1.0.0', $fixtureFolder . '/yoloupgrade.zip', true),
        ], $results2);
    }

    public function testCacheGenerationWithNoData()
    {
        $fileConfigurationStorageMock = $this->createMock(FileStorage::class);
        $fixtureFolder = sys_get_temp_dir() . '/ewww';

        $sourceProvider = new LocalSourceProvider($fixtureFolder, $fileConfigurationStorageMock);

        $fileConfigurationStorageMock->expects($this->once())->method('exists');
        $fileConfigurationStorageMock->expects($this->once())->method('save');
        $fileConfigurationStorageMock->expects($this->never())->method('load');

        $sourceProvider->getUpdatesOfModule('test1', '1.0.0');
        $sourceProvider->getUpdatesOfModule('test2', '1.0.0');
    }

    public function testCacheLoading()
    {
        $fileConfigurationStorageMock = $this->createMock(FileStorage::class);
        $fileConfigurationStorageMock->method('exists')->willReturn(true);
        $fileConfigurationStorageMock->method('load')->willReturn([]);

        $fixtureFolder = sys_get_temp_dir() . '/ewww';

        $sourceProvider = new LocalSourceProvider($fixtureFolder, $fileConfigurationStorageMock);

        $fileConfigurationStorageMock->expects($this->once())->method('exists');
        $fileConfigurationStorageMock->expects($this->once())->method('load');
        $fileConfigurationStorageMock->expects($this->never())->method('save');

        $sourceProvider->getUpdatesOfModule('test1', '1.0.0');
        $sourceProvider->getUpdatesOfModule('test2', '1.0.0');
    }
}
