<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;
use PrestaShop\Module\AutoUpgrade\Xml\ChecksumCompare;
use PrestaShop\Module\AutoUpgrade\Xml\FileLoader;

class ChecksumCompareTest extends TestCase
{
    public function setUp()
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }
    }

    public function testCompareReleases()
    {
        // Simplest test
        $v1Structure = [
            'composer.lock' => '75111ebd3d058964acc25a0df5f05db4',
            'init.php' => '0c7996ca741c35ec24e82e5b116604dc',
            'phpstan.neon.dist' => '7c19e03d141d22ceb8940ce0e4d71b01',
            'autoload.php' => '6207fe0a4016f9d9947be8e4b8e48b89',
            'app' => [
                'AppKernel.php' => '37d3976fc4877231fa652567e4e02cd4',
                'AppCache.php' => 'a04845405789c16d83832e9cba9b790c',
            ],
            'install' => [
                'index.php' => '0c7996ca741c35ec24e82e5b116604dc',
            ],
        ];
        $v2Structure = [
            'autoload.php' => '6207fe0a4016f9d9947be8e4b8e48b89',
            'init.php' => '0c7996ca741c35ec24e82e5b116604dc',
            'composer.lock' => 'd50e8124b90459a8e92633adcae892ff',
            'app' => [
                'AppCache.php' => 'a04845405789c16d83832e9cba9b790c',
                'AppKernel.php' => '1d6ea5b88cbf8e6b589760991a16094d',
            ],
            'install' => [
                'index.php' => 'c35ec24e741c35ed83832e5b116604dc',
            ],
        ];

        $checksumCompare = (new UpgradeContainer('/html', '/html/admin'))->getChecksumCompare();

        $actual = $checksumCompare->compareReleases($v1Structure, $v2Structure);
        $expected = [
            'deleted' => ['/phpstan.neon.dist'],
            'modified' => [
                '/composer.lock',
                '/app/AppKernel.php',
            ],
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testGetTamperedFilesOnShop()
    {
        $fileSystemAdapter = $this->getMockBuilder(FilesystemAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileLoader = $this->getMockBuilder(FileLoader::class)
            ->disableOriginalConstructor()
            ->setMethods(['getXmlMd5File'])
            ->getMock();

        $xmlFile = @simplexml_load_file(__DIR__ . '/../../fixtures/checksum-compare/8.1.0.xml');

        $fileLoader->method('getXmlMd5File')
            ->willReturn($xmlFile);

        $checksumCompare = new ChecksumCompare($fileLoader, $fileSystemAdapter, __DIR__ . '/../../fixtures/checksum-compare/8.1.0', __DIR__ . '/../../fixtures/checksum-compare/8.1.0/adminTest');
        $tamperedFiles = $checksumCompare->getTamperedFilesOnShop('8.1.0');

        $expected = [
            ChecksumCompare::CATEGORY_MAIL => [ChecksumCompare::FILE_MISSING => [], ChecksumCompare::FILE_ALTERED => []],
            ChecksumCompare::CATEGORY_TRANSLATION => [ChecksumCompare::FILE_MISSING => [], ChecksumCompare::FILE_ALTERED => ['translations/default/AdminActions.xlf']],
            ChecksumCompare::CATEGORY_CORE => [ChecksumCompare::FILE_MISSING => ['admin/init.php'], ChecksumCompare::FILE_ALTERED => ['admin/.htaccess']],
            ChecksumCompare::CATEGORY_THEME => [ChecksumCompare::FILE_MISSING => [], ChecksumCompare::FILE_ALTERED => ['themes/classic/config/theme.yml']],
        ];

        $this->assertEquals($expected, $tamperedFiles);
    }

    /**
     * Test that admin-api files are not incorrectly reported as missing
     * when admin folder has been renamed (bug from issue #40035)
     */
    public function testAdminApiFilesNotReportedAsMissingWithCustomAdminFolder()
    {
        $fileSystemAdapter = $this->getMockBuilder(FilesystemAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileLoader = $this->getMockBuilder(FileLoader::class)
            ->disableOriginalConstructor()
            ->setMethods(['getXmlMd5File'])
            ->getMock();

        $xmlFile = @simplexml_load_file(__DIR__ . '/../../fixtures/checksum-compare/9.0.0.xml');

        $fileLoader->method('getXmlMd5File')
            ->willReturn($xmlFile);

        // Use custom admin folder name to simulate real PrestaShop setup
        $checksumCompare = new ChecksumCompare(
            $fileLoader,
            $fileSystemAdapter,
            __DIR__ . '/../../fixtures/checksum-compare/9.0.0',
            __DIR__ . '/../../fixtures/checksum-compare/9.0.0/admin120df7jyx6dk20p2ehq'
        );
        $tamperedFiles = $checksumCompare->getTamperedFilesOnShop('9.0.0');

        // admin-api files should NOT be reported as missing
        // The bug was that str_replace would match "admin" in "admin-api"
        // and create incorrect paths like "admin120df7jyx6dk20p2ehq-api"
        $missingFiles = $tamperedFiles[ChecksumCompare::CATEGORY_CORE][ChecksumCompare::FILE_MISSING];
        $this->assertNotContains('admin-api/index.php', $missingFiles, 'admin-api files should not be reported as missing when admin folder is renamed');
        $this->assertNotContains('admin-api/.htaccess', $missingFiles, 'admin-api files should not be reported as missing when admin folder is renamed');
    }
}
