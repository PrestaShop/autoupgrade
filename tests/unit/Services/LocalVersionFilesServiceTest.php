<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace Services;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Exceptions\ZipActionException;
use PrestaShop\Module\AutoUpgrade\Services\LocalVersionFilesService;
use PrestaShop\Module\AutoUpgrade\Services\PrestashopVersionService;

class LocalVersionFilesServiceTest extends TestCase
{
    /** @var PrestashopVersionService */
    private $prestashopVersionService;

    public function setUp()
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }

        $this->prestashopVersionService = $this->getMockBuilder(PrestashopVersionService::class)
            ->disableOriginalConstructor()
            ->setMethods(['extractPrestashopVersionFromZip', 'extractPrestashopVersionFromXml'])
            ->getMock();
    }

    /**
     * @throws ZipActionException
     */
    public function testGetLocalVersionsFiles()
    {
        $this->prestashopVersionService->method('extractPrestashopVersionFromZip')
            ->withConsecutive(
                ['8.1.7.zip'],
                ['1.7.8.10.zip']
            )
            ->willReturnOnConsecutiveCalls(
                '8.1.7',
                '1.7.8.10'
            );
        $this->prestashopVersionService->method('extractPrestashopVersionFromXml')
            ->withConsecutive(
                ['8.1.7.xml'],
                ['1.7.8.10.xml']
            )
            ->willReturnOnConsecutiveCalls(
                '8.1.7',
                '1.7.8.10'
            );

        $localVersionFilesService = $this->getMockBuilder(LocalVersionFilesService::class)
            ->setConstructorArgs([$this->prestashopVersionService, 'fakeDownloadPath', '1.7.8.11'])
            ->setMethods(['getAllFilesFromFolder'])
            ->getMock();

        $localVersionFilesService->method('getAllFilesFromFolder')
            ->withConsecutive(
                ['fakeDownloadPath', LocalVersionFilesService::TYPE_ZIP],
                ['fakeDownloadPath', LocalVersionFilesService::TYPE_XML]
            )
            ->willReturnOnConsecutiveCalls(
                ['8.1.7.zip', '1.7.8.10.zip'],
                ['8.1.7.xml', '1.7.8.10.xml']
            );

        $expected = [
            '8.1.7' => [
                'xml' => ['8.1.7.xml'],
                'zip' => ['8.1.7.zip'],
            ],
        ];

        $this->assertEquals($expected, $localVersionFilesService->getLocalVersionsFiles());
    }

    /**
     * @throws ZipActionException
     */
    public function testGetLocalVersionsFilesWithMissingFile()
    {
        $this->prestashopVersionService->method('extractPrestashopVersionFromZip')
            ->withConsecutive(
                ['8.1.7.zip'],
                ['9.0.0.zip']
            )
            ->willReturnOnConsecutiveCalls(
                '8.1.7',
                '9.0.0'
            );
        $this->prestashopVersionService->method('extractPrestashopVersionFromXml')
            ->withConsecutive(
                ['8.1.7.xml']
            )
            ->willReturnOnConsecutiveCalls(
                '8.1.7'
            );

        $localVersionFilesService = $this->getMockBuilder(LocalVersionFilesService::class)
            ->setConstructorArgs([$this->prestashopVersionService, 'fakeDownloadPath', '1.7.8.11'])
            ->setMethods(['getAllFilesFromFolder'])
            ->getMock();

        $localVersionFilesService->method('getAllFilesFromFolder')
            ->withConsecutive(
                ['fakeDownloadPath', LocalVersionFilesService::TYPE_ZIP],
                ['fakeDownloadPath', LocalVersionFilesService::TYPE_XML]
            )
            ->willReturnOnConsecutiveCalls(
                ['8.1.7.zip', '9.0.0.zip'],
                ['8.1.7.xml']
            );

        $expected = [
            '8.1.7' => [
                'xml' => ['8.1.7.xml'],
                'zip' => ['8.1.7.zip'],
            ],
        ];

        $this->assertEquals($expected, $localVersionFilesService->getLocalVersionsFiles());
    }

    /**
     * @throws ZipActionException
     */
    public function testGetFlatZipAndXmlLists()
    {
        $this->prestashopVersionService->method('extractPrestashopVersionFromZip')
            ->withConsecutive(
                ['1.7.7.7.zip'],
                ['8.1.7.zip'],
                ['8.2.0.zip'],
                ['9.0.0.zip']
            )
            ->willReturnOnConsecutiveCalls(
                '1.7.7.7',
                '8.1.7',
                '8.2.0',
                '9.0.0'
            );
        $this->prestashopVersionService->method('extractPrestashopVersionFromXml')
            ->withConsecutive(
                ['1.7.7.7.xml'],
                ['8.1.7.xml'],
                ['8.2.0.xml']
            )
            ->willReturnOnConsecutiveCalls(
                '1.7.7.7',
                '8.1.7',
                '8.2.0'
            );

        $localVersionFilesService = $this->getMockBuilder(LocalVersionFilesService::class)
            ->setConstructorArgs([$this->prestashopVersionService, 'fakeDownloadPath', '1.7.8.11'])
            ->setMethods(['getAllFilesFromFolder'])
            ->getMock();

        $localVersionFilesService->method('getAllFilesFromFolder')
            ->withConsecutive(
                ['fakeDownloadPath', LocalVersionFilesService::TYPE_ZIP],
                ['fakeDownloadPath', LocalVersionFilesService::TYPE_XML]
            )
            ->willReturnOnConsecutiveCalls(
                ['1.7.7.7.zip', '8.1.7.zip', '8.2.0.zip', '9.0.0.zip'],
                ['1.7.7.7.xml', '8.1.7.xml', '8.2.0.xml']
            );

        $expected = [
            'xml' => ['8.1.7.xml', '8.2.0.xml'],
            'zip' => ['8.1.7.zip', '8.2.0.zip'],
        ];

        $this->assertEquals($expected, $localVersionFilesService->getFlatZipAndXmlLists());
    }
}
