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

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Services\ComposerService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider\PrestaShopArchiveSourceProvider;

class PrestaShopArchiveSourceProviderTest extends TestCase
{
    private $fixtureFolder;

    protected function setUp()
    {
        parent::setUp();

        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');

            return;
        }

        $this->fixtureFolder = sys_get_temp_dir() . '/prestashop_archive_test_' . uniqid();
        if (!is_dir($this->fixtureFolder)) {
            mkdir($this->fixtureFolder);
        }
        if (!is_dir($this->fixtureFolder . '/modules')) {
            mkdir($this->fixtureFolder . '/modules');
        }
    }

    protected function tearDown()
    {
        $this->deleteDirectory($this->fixtureFolder);
        parent::tearDown();
    }

    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    public function testCacheGenerationWithData()
    {
        $composerServiceMock = $this->createMock(ComposerService::class);
        $fileConfigurationStorageMock = $this->createMock(FileStorage::class);
        $moduleAdapterMock = $this->createMock(ModuleAdapter::class);

        // Setup modules in modules folder
        mkdir($this->fixtureFolder . '/modules/module_a');
        mkdir($this->fixtureFolder . '/modules/module_b');
        mkdir($this->fixtureFolder . '/modules/module_c');
        mkdir($this->fixtureFolder . '/modules/module_d');

        // module_a is in composer.lock, should be skipped
        // module_b is NOT in composer.lock, should be kept
        // module_c is NOT in composer.lock, but not a valid module folder, should be skipped
        // module_d is NOT in composer.lock, is a valid folder, but version missing, should be skipped

        $composerServiceMock->method('getModulesInComposerLock')->willReturn([
            ['name' => 'module_a'],
        ]);

        $moduleAdapterMock->method('isFolderContainingModule')->willReturnMap([
            [$this->fixtureFolder . '/modules/.', false],
            [$this->fixtureFolder . '/modules/..', false],
            [$this->fixtureFolder . '/modules/module_a', false],
            [$this->fixtureFolder . '/modules/module_b', true],
            [$this->fixtureFolder . '/modules/module_c', false],
            [$this->fixtureFolder . '/modules/module_d', true],
        ]);

        $moduleAdapterMock->method('getVersionFromConfigXmlInFolder')->willReturnMap([
            [$this->fixtureFolder . '/modules/module_b', '2.0.0'],
            [$this->fixtureFolder . '/modules/module_d', null],
        ]);

        $sourceProvider = new PrestaShopArchiveSourceProvider(
            $this->fixtureFolder,
            $composerServiceMock,
            $fileConfigurationStorageMock,
            $moduleAdapterMock
        );

        $fileConfigurationStorageMock->expects($this->once())->method('exists')->with(UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_PRESTASHOP_ARCHIVE)->willReturn(false);
        $fileConfigurationStorageMock->expects($this->once())->method('save')->with(
            [
                new ModuleSource('module_b', '2.0.0', $this->fixtureFolder . '/modules/module_b', false),
            ],
            UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_PRESTASHOP_ARCHIVE
        );

        $results = $sourceProvider->getUpdatesOfModule('module_b', '1.0.0');

        $this->assertCount(1, $results);
        $this->assertEquals('module_b', $results[0]->getName());
        $this->assertEquals('2.0.0', $results[0]->getNewVersion());
    }

    public function testCacheLoading()
    {
        $composerServiceMock = $this->createMock(ComposerService::class);
        $fileConfigurationStorageMock = $this->createMock(FileStorage::class);
        $moduleAdapterMock = $this->createMock(ModuleAdapter::class);

        $cachedData = [
            new ModuleSource('module_cached', '3.0.0', '/path/to/module_cached', false),
        ];

        $fileConfigurationStorageMock->method('exists')->willReturn(true);
        $fileConfigurationStorageMock->method('load')->willReturn($cachedData);

        $sourceProvider = new PrestaShopArchiveSourceProvider(
            $this->fixtureFolder,
            $composerServiceMock,
            $fileConfigurationStorageMock,
            $moduleAdapterMock
        );

        $fileConfigurationStorageMock->expects($this->once())->method('exists');
        $fileConfigurationStorageMock->expects($this->once())->method('load');
        $fileConfigurationStorageMock->expects($this->never())->method('save');
        $composerServiceMock->expects($this->never())->method('getModulesInComposerLock');

        $results = $sourceProvider->getUpdatesOfModule('module_cached', '1.0.0');

        $this->assertEquals($cachedData, $results);
    }
}
