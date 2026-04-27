<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
