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
use PrestaShop\Module\AutoUpgrade\Progress\Backlog;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class ZipActionTest extends TestCase
{
    const ZIP_CONTENT_PATH = __DIR__ . '/../fixtures/ArchiveExample/ArchiveExample.zip';
    const IDENTICAL_CONTENT_FILE_PATH = __DIR__ . '/../fixtures/ArchiveExample/dummyFolder/AppKernelExample.php.txt';
    const NOT_IDENTICAL_CONTENT_FILE_PATH = __DIR__ . '/../fixtures/AppKernelExample.php.txt';

    private $container;
    private $contentExcepted;

    protected function setUp()
    {
        $this->contentExcepted = [
            'dummyFolder/',
            'dummyFolder/AppKernelExample.php.txt',
        ];

        $this->container = new UpgradeContainer(__DIR__, __DIR__ . '/..');
    }

    public function testArchiveContentWithZipArchive()
    {
        $zipAction = $this->container->getZipAction();
        $this->assertSame($this->contentExcepted, $zipAction->listContent(self::ZIP_CONTENT_PATH));
    }

    public function testCreateArchiveWithZipArchive()
    {
        $newZipPath = tempnam(sys_get_temp_dir(), 'mod');

        $zipAction = $this->container->getZipAction();
        $backlog = new Backlog([__FILE__], 1);
        $this->assertSame(true, $zipAction->compress($backlog, $newZipPath));

        // Cleanup
        unlink($newZipPath);
    }

    public function testExtractArchiveWithZipArchive()
    {
        // Get tmp folder
        $destinationFolder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();

        $zipAction = $this->container->getZipAction();
        $this->assertSame(true, $zipAction->extract(self::ZIP_CONTENT_PATH, $destinationFolder));

        // We check the files were actually extracted
        foreach ($this->contentExcepted as $file) {
            $completePath = $destinationFolder . DIRECTORY_SEPARATOR . $file;
            $this->assertTrue(
                is_dir($completePath) || (file_exists($completePath) && filesize($completePath)),
                "$completePath does not exist"
            );
        }
    }

    public function testIsFileUnchanged()
    {
        $zipAction = $this->container->getZipAction();

        $zip = new ZipArchive();
        $zip->open(self::ZIP_CONTENT_PATH);

        $this->assertTrue($zipAction->isFileUnchanged(self::IDENTICAL_CONTENT_FILE_PATH, 'dummyFolder/AppKernelExample.php.txt', $zip));
        $this->assertFalse($zipAction->isFileUnchanged(self::NOT_IDENTICAL_CONTENT_FILE_PATH, 'dummyFolder/AppKernelExample.php.txt', $zip));
    }
}
