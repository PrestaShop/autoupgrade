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
                'display_name' => '',
                'tab' => '',
                'description' => '',
                'author' => '',
                'prestashop_min_version' => '',
                'icon' => '',
            ],
            'ps_apiresources' => [
                'version' => '1.0.0',
                'download_url' => 'https://example.com/ps_apiresources.zip',
                'display_name' => '',
                'tab' => '',
                'description' => '',
                'author' => '',
                'prestashop_min_version' => '',
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
