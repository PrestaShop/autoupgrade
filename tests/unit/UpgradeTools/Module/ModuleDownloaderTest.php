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
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleDownloader;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleDownloaderContext;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSourceAggregate;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class ModuleDownloaderTest extends TestCase
{
    /** @var ModuleDownloader */
    private $moduleDownloader;

    /** @var PHPUnit_Framework_MockObject_MockObject|Logger|(Logger&PHPUnit_Framework_MockObject_MockObject) */
    private $logger;

    public static function setUpBeforeClass()
    {
        require_once __DIR__ . '/Source/Provider/ModuleSourceProviderMock.php';
        @mkdir(sys_get_temp_dir() . '/fakeDownloaderDestination');
    }

    protected function setUp()
    {
        parent::setUp();

        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }

        $translator = $this->createMock(Translator::class);
        $translator->method('trans')
            ->willReturnCallback(function ($message, $parameters = []) {
                return vsprintf($message, $parameters);
            });

        $this->logger = $this->createMock(Logger::class);
        $this->moduleDownloader = new ModuleDownloader($translator, $this->logger, sys_get_temp_dir() . '/fakeDownloaderDestination');
    }

    public function testModuleDownloaderSucceedsOnFirstTryWithLocalFile()
    {
        $moduleContext = new ModuleDownloaderContext(['name' => 'mymodule', 'currentVersion' => '1.0.0']);

        $dummyProvider1 = (new ModuleSourceProviderMock())->setSources([
            new ModuleSource('mymodule', '2.0.0', realpath(__DIR__ . '/../../../fixtures/mymodule'), false),
            new ModuleSource('mymodule', '1.2.0', realpath(__DIR__ . '/../../../fixtures/ArchiveExample.zip'), false),
        ]);
        $moduleSourceList = new ModuleSourceAggregate([$dummyProvider1]);

        $moduleSourceList->setSourcesIn($moduleContext);

        $this->logger->expects($this->once())
            ->method('notice')
            ->with('Module mymodule update files (1.0.0 => 2.0.0) have been fetched from ' . realpath(__DIR__ . '/../../../fixtures/mymodule') . '.');

        $this->moduleDownloader->downloadModule($moduleContext);

        // Only the first download should have run
        $this->assertEquals('/tmp/fakeDownloaderDestination', $moduleContext->getPathToModuleUpdate());
    }

    public function testModuleDownloaderSucceedsOnFirstTryWithRemoteFile()
    {
        $moduleContext = new ModuleDownloaderContext(['name' => 'autoupgrade', 'currentVersion' => '1.0.0']);

        $dummyProvider1 = (new ModuleSourceProviderMock())->setSources([
            new ModuleSource('autoupgrade', '6.0.0', 'https://github.com/PrestaShop/autoupgrade/releases/download/v6.0.0/autoupgrade-v6.0.0.zip', true),
        ]);
        $moduleSourceList = new ModuleSourceAggregate([$dummyProvider1]);

        $moduleSourceList->setSourcesIn($moduleContext);

        $this->logger->expects($this->once())
            ->method('notice')
            ->with('Module autoupgrade update files (1.0.0 => 6.0.0) have been fetched from https://github.com/PrestaShop/autoupgrade/releases/download/v6.0.0/autoupgrade-v6.0.0.zip.');

        $this->moduleDownloader->downloadModule($moduleContext);
    }

    public function testModuleDownloaderFails()
    {
        $moduleContext = new ModuleDownloaderContext(['name' => 'mymodule', 'currentVersion' => '1.0.0']);

        $dummyProvider1 = (new ModuleSourceProviderMock())->setSources([
            new ModuleSource('mymodule', '2.0.0', '/non-existing-folder', false),
        ]);
        $moduleSourceList = new ModuleSourceAggregate([$dummyProvider1]);

        $moduleSourceList->setSourcesIn($moduleContext);

        $this->logger->expects($this->exactly(2))
            ->method('debug')
            ->withConsecutive(
                PHP_VERSION_ID >= 80000 ? ['RecursiveDirectoryIterator::__construct(/non-existing-folder): Failed to open directory: No such file or directory']
                    : ['RecursiveDirectoryIterator::__construct(/non-existing-folder): failed to open dir: No such file or directory'], ['Download of source #0 has failed.']
            );
        $this->expectExceptionMessage('All download attempts have failed. Check your environment and try again.');

        $this->moduleDownloader->downloadModule($moduleContext);
    }

    public function testModuleDownloaderHandlesFallbacks()
    {
        $moduleContext = new ModuleDownloaderContext(['name' => 'mymodule', 'currentVersion' => '1.0.0']);

        $dummyProvider1 = (new ModuleSourceProviderMock())->setSources([
            new ModuleSource('mymodule', '2.0.0', '/non-existing-folder', false),
        ]);
        $dummyProvider2 = (new ModuleSourceProviderMock())->setSources([
            new ModuleSource('mymodule', '2.0.0', realpath(__DIR__ . '/../../../fixtures/mymodule'), false),
        ]);
        $moduleSourceList = new ModuleSourceAggregate([$dummyProvider1, $dummyProvider2]);

        $moduleSourceList->setSourcesIn($moduleContext);

        $this->logger->expects($this->exactly(2))
            ->method('debug')
            ->withConsecutive(
                PHP_VERSION_ID >= 80000 ? ['RecursiveDirectoryIterator::__construct(/non-existing-folder): Failed to open directory: No such file or directory']
                    : ['RecursiveDirectoryIterator::__construct(/non-existing-folder): failed to open dir: No such file or directory'],
                ['Download of source #0 has failed.']
            );
        $this->logger->expects($this->once())
            ->method('notice')
            ->with('Module mymodule update files (1.0.0 => 2.0.0) have been fetched from ' . realpath(__DIR__ . '/../../../fixtures/mymodule') . '.');

        $this->moduleDownloader->downloadModule($moduleContext);
    }

    public function testDetectionOfXmlFileInDownloadedContents()
    {
        $moduleContext = new ModuleDownloaderContext(['name' => 'mymodule', 'currentVersion' => '1.0.0']);

        $dummyProvider1 = (new ModuleSourceProviderMock())->setSources([
            new ModuleSource('badmodule', '2.0.0', realpath(__DIR__ . '/../../../../') . '/config.xml', true),
        ]);
        $moduleSourceList = new ModuleSourceAggregate([$dummyProvider1]);

        $moduleSourceList->setSourcesIn($moduleContext);

        $this->logger->expects($this->exactly(2))
            ->method('debug')
            ->withConsecutive(
                ['Invalid contents from provider (Got an XML file).'],
                ['Download of source #0 has failed.']
            );
        $this->expectExceptionMessage('All download attempts have failed. Check your environment and try again.');

        $this->moduleDownloader->downloadModule($moduleContext);
    }
}
