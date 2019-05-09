<?php
/*
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2019 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
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
