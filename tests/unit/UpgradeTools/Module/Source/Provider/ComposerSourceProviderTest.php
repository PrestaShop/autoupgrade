<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Services\ComposerService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider\ComposerSourceProvider;
use Symfony\Component\Filesystem\Filesystem;

class ComposerSourceProviderTest extends TestCase
{
    public function testCacheGenerationWithData()
    {
        $prestashopContents = realpath(__DIR__ . '/../../../../../fixtures/prestashop-release');
        $fileConfigurationStorageMock = $this->createMock(FileStorage::class);

        $sourceProvider = new ComposerSourceProvider($prestashopContents, new ComposerService(new Filesystem()), $fileConfigurationStorageMock);

        $fileConfigurationStorageMock->expects($this->once())->method('exists');
        $fileConfigurationStorageMock->expects($this->once())->method('save');
        $fileConfigurationStorageMock->expects($this->never())->method('load');

        $results1 = $sourceProvider->getUpdatesOfModule('contactform', '0.9.0');
        $results2 = $sourceProvider->getUpdatesOfModule('contactform', '0.9.0');

        $this->assertEquals($results1, $results2);
        $this->assertEquals([
            new ModuleSource('contactform', '3.0.0', $prestashopContents . '/modules/contactform', false),
        ], $results2);
    }

    public function testCacheGenerationWithNoData()
    {
        // root project composer.lock
        $prestashopContents = realpath(__DIR__ . '/../../../../../../');
        $fileConfigurationStorageMock = $this->createMock(FileStorage::class);

        $sourceProvider = new ComposerSourceProvider($prestashopContents, new ComposerService(new Filesystem()), $fileConfigurationStorageMock);

        $fileConfigurationStorageMock->expects($this->once())->method('exists');
        $fileConfigurationStorageMock->expects($this->once())->method('save');
        $fileConfigurationStorageMock->expects($this->never())->method('load');

        $sourceProvider->getUpdatesOfModule('test1', '1.0.0');
        $sourceProvider->getUpdatesOfModule('test2', '1.0.0');
    }

    public function testCacheLoading()
    {
        $prestashopContents = realpath(__DIR__ . '/../../../../../prestashop-release');
        $fileConfigurationStorageMock = $this->createMock(FileStorage::class);
        $fileConfigurationStorageMock->method('exists')->willReturn(true);
        $fileConfigurationStorageMock->method('load')->willReturn([]);

        $sourceProvider = new ComposerSourceProvider($prestashopContents, new ComposerService(new Filesystem()), $fileConfigurationStorageMock);

        $fileConfigurationStorageMock->expects($this->once())->method('exists');
        $fileConfigurationStorageMock->expects($this->once())->method('load');
        $fileConfigurationStorageMock->expects($this->never())->method('save');

        $sourceProvider->getUpdatesOfModule('test1', '1.0.0');
        $sourceProvider->getUpdatesOfModule('test2', '1.0.0');
    }
}
