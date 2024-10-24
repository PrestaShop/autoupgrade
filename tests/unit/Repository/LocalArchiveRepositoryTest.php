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
use PrestaShop\Module\AutoUpgrade\Repository\LocalArchiveRepository;

class LocalArchiveRepositoryTest extends TestCase
{
    private $downloadPath;
    private $repository;

    protected function setUp()
    {
        $this->downloadPath = __DIR__ . '/../../fixtures/repository/localArchives/';
        if (!is_dir($this->downloadPath)) {
            mkdir($this->downloadPath, 0777, true);
        }
        $this->repository = new LocalArchiveRepository($this->downloadPath);
    }

    protected function tearDown()
    {
        array_map('unlink', glob($this->downloadPath . '/*'));
        rmdir($this->downloadPath);
    }

    public function testGetZipLocalArchiveReturnsEmptyArrayWhenNoFiles()
    {
        $this->assertEquals(false, $this->repository->getZipLocalArchive());
    }

    public function testGetXmlLocalArchiveReturnsEmptyArrayWhenNoFiles()
    {
        $this->assertEquals(false, $this->repository->getXmlLocalArchive());
    }

    public function testGetZipLocalArchiveReturnsZipFiles()
    {
        $zipName = 'test.zip';
        $zipFile = $this->downloadPath . '/' . $zipName;
        touch($zipFile);

        $this->assertSame([$zipName], $this->repository->getZipLocalArchive());
    }

    public function testGetXmlLocalArchiveReturnsXmlFiles()
    {
        $xmlName = 'test.xml';
        $xmlFile = $this->downloadPath . '/' . $xmlName;
        touch($xmlFile);

        $this->assertSame([$xmlName], $this->repository->getXmlLocalArchive());
    }

    public function testHasLocalArchiveReturnsFalseWhenNoFiles()
    {
        $this->assertFalse($this->repository->hasLocalArchive());
    }

    public function testHasLocalArchiveReturnsFalseWhenOnlyZipFilesExist()
    {
        $zipFile = $this->downloadPath . '/test.zip';
        touch($zipFile);

        $this->assertFalse($this->repository->hasLocalArchive());
    }

    public function testHasLocalArchiveReturnsFalseWhenOnlyXmlFilesExist()
    {
        $xmlFile = $this->downloadPath . '/test.xml';
        touch($xmlFile);

        $this->assertFalse($this->repository->hasLocalArchive());
    }

    public function testHasLocalArchiveReturnsTrueWhenBothZipAndXmlFilesExist()
    {
        $zipFile = $this->downloadPath . '/test.zip';
        $xmlFile = $this->downloadPath . '/test.xml';
        touch($zipFile);
        touch($xmlFile);

        $this->assertTrue($this->repository->hasLocalArchive());
    }
}
