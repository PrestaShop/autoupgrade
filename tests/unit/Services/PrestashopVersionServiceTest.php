<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Services\PrestashopVersionService;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class PrestashopVersionServiceTest extends TestCase
{
    /**
     * @var PrestashopVersionService
     */
    private $versionService;

    protected function setUp(): void
    {
        $this->container = new UpgradeContainer('/html', '/html/admin');

        $this->fixturePath = __DIR__ . '/../../fixtures/localChannel/';

        $this->versionService = new PrestashopVersionService($this->container->getZipAction(), new Filesystem());
    }

    public function testExtractPrestashopVersionFromZipFileNotFound(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('Unable to find missing.zip file');

        $this->versionService->extractPrestashopVersionFromZip('missing.zip');
    }

    public function testExtractPrestashopVersionFromZipInvalidContent(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to extract version from content');

        $this->versionService->extractPrestashopVersionFromZip($this->fixturePath . 'not_versioned_8.2.0.zip');
    }

    public function testExtractPrestashopVersionFromZipSuccess(): void
    {
        $result = $this->versionService->extractPrestashopVersionFromZip($this->fixturePath . 'versioned_8.2.0.zip');
        $this->assertEquals('8.2.0', $result);
    }

    public function testExtractPrestashopVersionFromXmlInvalidContent(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Prestashop version not found in file ' . $this->fixturePath . 'not_versioned_8.2.0.xml');

        $this->versionService->extractPrestashopVersionFromXml($this->fixturePath . 'not_versioned_8.2.0.xml');
    }

    public function testExtractPrestashopVersionFromXmlSuccess(): void
    {
        $result = $this->versionService->extractPrestashopVersionFromXml($this->fixturePath . 'versioned_8.2.0.xml');
        $this->assertEquals('8.2.0', $result);
    }
}
