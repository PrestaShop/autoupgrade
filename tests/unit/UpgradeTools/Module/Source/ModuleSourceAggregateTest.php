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
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleDownloaderContext;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSourceAggregate;

class ModuleSourceAggregateTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        require_once __DIR__ . '/Provider/ModuleSourceProviderMock.php';
    }

    public function testUpdateSourcesAreSet()
    {
        $dummyProvider1 = (new ModuleSourceProviderMock())->setSources([
            new ModuleSource('Module1', '3.0.0', __DIR__, false),
        ]);
        $dummyProvider2 = (new ModuleSourceProviderMock())->setSources([
            new ModuleSource('Module1', '2.0.0', __DIR__ . '.zip', true),
        ]);
        $moduleSourceList = new ModuleSourceAggregate([$dummyProvider1, $dummyProvider2]);

        $moduleContext = new ModuleDownloaderContext(['name' => 'Module1', 'currentVersion' => '1.0.0']);

        $moduleSourceList->setSourcesIn($moduleContext);

        $results = $moduleContext->getUpdateSources();

        $this->assertSame(2, count($results));
        $this->assertEquals(['name' => 'Module1', 'newVersion' => '3.0.0', 'path' => __DIR__, 'unzipable' => false], $results[0]->toArray());
        $this->assertEquals(['name' => 'Module1', 'newVersion' => '2.0.0', 'path' => __DIR__ . '.zip', 'unzipable' => true], $results[1]->toArray());
    }

    public function testUpdateSourcesAreOrderedByVersion()
    {
        $dummyProvider1 = (new ModuleSourceProviderMock())->setSources([
            new ModuleSource('Module1', '2.0.0', __DIR__ . '/1', false),
            new ModuleSource('Module1', '4.0.0', __DIR__ . '/2', false),
        ]);
        $dummyProvider2 = (new ModuleSourceProviderMock())->setSources([
            new ModuleSource('Module1', '3.0.0', __DIR__ . '.zip', true),
        ]);
        $moduleSourceList = new ModuleSourceAggregate([$dummyProvider1, $dummyProvider2]);

        $moduleContext = new ModuleDownloaderContext(['name' => 'Module1', 'currentVersion' => '1.0.0']);

        $moduleSourceList->setSourcesIn($moduleContext);

        $results = $moduleContext->getUpdateSources();

        $this->assertSame(3, count($results));
        $this->assertEquals(['name' => 'Module1', 'newVersion' => '4.0.0', 'path' => __DIR__ . '/2', 'unzipable' => false], $results[0]->toArray());
        $this->assertEquals(['name' => 'Module1', 'newVersion' => '3.0.0', 'path' => __DIR__ . '.zip', 'unzipable' => true], $results[1]->toArray());
        $this->assertEquals(['name' => 'Module1', 'newVersion' => '2.0.0', 'path' => __DIR__ . '/1', 'unzipable' => false], $results[2]->toArray());
    }

    public function testUpdateSourcesAreOrderedByProviderPriority()
    {
        $dummyProvider1 = (new ModuleSourceProviderMock())->setSources([
            new ModuleSource('Module1', '2.0.0', __DIR__ . '/1/2.0.0', false),
            new ModuleSource('Module1', '4.0.0', __DIR__ . '/1/4.0.0', false),
        ]);
        $dummyProvider2 = (new ModuleSourceProviderMock())->setSources([
            new ModuleSource('Module1', '3.0.0', __DIR__ . '/2/3.0.0', false),
            new ModuleSource('Module1', '4.0.0', __DIR__ . '/2/4.0.0', false),
            new ModuleSource('Module1', '2.0.0', __DIR__ . '/2/2.0.0', false),
        ]);
        $moduleSourceList = new ModuleSourceAggregate([$dummyProvider1, $dummyProvider2]);

        $moduleContext = new ModuleDownloaderContext(['name' => 'Module1', 'currentVersion' => '1.0.0']);

        $moduleSourceList->setSourcesIn($moduleContext);

        $results = $moduleContext->getUpdateSources();

        $this->assertSame(5, count($results));
        $this->assertEquals(['name' => 'Module1', 'newVersion' => '4.0.0', 'path' => __DIR__ . '/1/4.0.0', 'unzipable' => false], $results[0]->toArray());
        $this->assertEquals(['name' => 'Module1', 'newVersion' => '4.0.0', 'path' => __DIR__ . '/2/4.0.0', 'unzipable' => false], $results[1]->toArray());
        $this->assertEquals(['name' => 'Module1', 'newVersion' => '3.0.0', 'path' => __DIR__ . '/2/3.0.0', 'unzipable' => false], $results[2]->toArray());
        $this->assertEquals(['name' => 'Module1', 'newVersion' => '2.0.0', 'path' => __DIR__ . '/1/2.0.0', 'unzipable' => false], $results[3]->toArray());
        $this->assertEquals(['name' => 'Module1', 'newVersion' => '2.0.0', 'path' => __DIR__ . '/2/2.0.0', 'unzipable' => false], $results[4]->toArray());
    }
}
