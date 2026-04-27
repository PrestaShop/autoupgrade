<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Progress\Backlog;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\Filesystem\Filesystem;

class ZipActionTest extends TestCase
{
    const ZIP_CONTENT_PATH = __DIR__ . '/../fixtures/ArchiveExample/ArchiveExample.zip';
    const IDENTICAL_CONTENT_FILE_PATH = __DIR__ . '/../fixtures/ArchiveExample/dummyFolder/AppKernelExample.php.txt';
    const NOT_IDENTICAL_CONTENT_FILE_PATH = __DIR__ . '/../fixtures/AppKernelExample.php.txt';

    /** @var UpgradeContainer */
    private $container;
    private $contentExcepted;

    protected function setUp()
    {
        $this->contentExcepted = [
            'dummyFolder/',
            'dummyFolder/AppKernelExample.php.txt',
        ];

        $rootDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $this->container = new UpgradeContainer($rootDir, $rootDir . DIRECTORY_SEPARATOR . 'admin');
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

    public function testCompleteCompressionAndExtractionOfFiles()
    {
        $filesystem = new Filesystem();

        // Create contents to be zipped and unzipped
        $sourceFolder = $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH);
        $destinationFolder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $temporaryZipFile = tempnam(sys_get_temp_dir(), 'mod');

        $filesystem->mkdir([
            $sourceFolder,
            $sourceFolder . DIRECTORY_SEPARATOR . 'folder/folder2',
            $destinationFolder,
        ]);
        $filesystem->touch([
            $sourceFolder . DIRECTORY_SEPARATOR . 'file1.txt',
            $sourceFolder . DIRECTORY_SEPARATOR . 'file2.txt',
            $sourceFolder . DIRECTORY_SEPARATOR . 'folder/file3.txt',
        ]);
        $filesystem->symlink(
            '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'file2.txt',
            $sourceFolder . DIRECTORY_SEPARATOR . 'folder/folder2/file4.txt'
        );

        $backlog = new Backlog([
            $sourceFolder . DIRECTORY_SEPARATOR . 'file1.txt',
            $sourceFolder . DIRECTORY_SEPARATOR . 'file2.txt',
            $sourceFolder . DIRECTORY_SEPARATOR . 'folder/file3.txt',
            $sourceFolder . DIRECTORY_SEPARATOR . 'folder/folder2/file4.txt',
        ], 4);

        // Run
        $zipAction = $this->container->getZipAction();
        $resultOfCompress = $zipAction->compress($backlog, $temporaryZipFile);
        $resultOfExtract = $zipAction->extract($temporaryZipFile, $destinationFolder);

        // Check
        $this->assertTrue($resultOfCompress);
        $this->assertTrue($resultOfExtract);

        $this->assertTrue(is_dir($destinationFolder . DIRECTORY_SEPARATOR . 'folder'));
        $this->assertTrue(is_dir($destinationFolder . DIRECTORY_SEPARATOR . 'folder/folder2'));

        $this->assertTrue(is_file($destinationFolder . DIRECTORY_SEPARATOR . 'file1.txt'));
        $this->assertTrue(is_file($destinationFolder . DIRECTORY_SEPARATOR . 'file2.txt'));
        $this->assertTrue(is_file($destinationFolder . DIRECTORY_SEPARATOR . 'folder/file3.txt'));

        $this->assertTrue(is_link($destinationFolder . DIRECTORY_SEPARATOR . 'folder/folder2/file4.txt'));
        $this->assertSame(
            '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'file2.txt',
            readlink($destinationFolder . DIRECTORY_SEPARATOR . 'folder/folder2/file4.txt')
        );
    }

    public function testCompressedFilesAreSimlinks()
    {
        $zipAction = $this->container->getZipAction();

        $this->assertFalse($zipAction->isCompressedFileASymLink(2179792896, 'file.txt'));
        $this->assertFalse($zipAction->isCompressedFileASymLink(2180841472, 'file.txt'));
        $this->assertTrue($zipAction->isCompressedFileASymLink(2717843456, 'file.txt'));
    }
}
