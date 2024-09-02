<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\FileConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider\LocalSourceProvider;

class LocalSourceProviderTest extends TestCase
{
    public function testCacheGenerationWithData()
    {
        $fileConfigurationStorageMock = $this->createMock(FileConfigurationStorage::class);

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
        $fileConfigurationStorageMock = $this->createMock(FileConfigurationStorage::class);
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
        $fileConfigurationStorageMock = $this->createMock(FileConfigurationStorage::class);
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
