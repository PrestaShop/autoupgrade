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
        $fileConfigurationStorageMock = $this->createMock(FileConfigurationStorage::class);

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
        $fileConfigurationStorageMock = $this->createMock(FileConfigurationStorage::class);

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
        $fileConfigurationStorageMock = $this->createMock(FileConfigurationStorage::class);
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
