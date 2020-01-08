<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class ZipActionTest extends TestCase
{
    const ZIP_CONTENT_PATH = __DIR__ . '/fixtures/ArchiveExample.zip';

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
        $files = [__FILE__];
        $this->assertSame(true, $zipAction->compress($files, $newZipPath));

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
}
