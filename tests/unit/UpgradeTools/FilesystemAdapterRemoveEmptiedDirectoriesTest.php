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
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FileFilter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;
use Symfony\Component\Filesystem\Filesystem;

class FilesystemAdapterRemoveEmptiedDirectoriesTest extends TestCase
{
    /** @var string */
    private $root;

    /** @var Filesystem */
    private $filesystem;

    protected function setUp()
    {
        parent::setUp();
        $this->filesystem = new Filesystem();
        $this->root = sys_get_temp_dir() . '/autoupgrade-restore-' . uniqid();
        $this->filesystem->mkdir($this->root);
    }

    protected function tearDown()
    {
        $this->filesystem->remove($this->root);
        parent::tearDown();
    }

    public function testTheFoldersOfAModuleAddedByTheUpdateAreRemoved()
    {
        $this->createFiles(['/modules/oldmodule/oldmodule.php']);
        $this->createDirectories(['/modules/newmodule/views/js']);

        $this->removeEmptiedDirectories(
            ['/modules/newmodule/newmodule.php', '/modules/newmodule/views/js/front.js'],
            ['/modules/oldmodule/oldmodule.php']
        );

        $this->assertDirectoryNotExists($this->root . '/modules/newmodule');
        $this->assertDirectoryExists($this->root . '/modules/oldmodule');
    }

    public function testAFolderStillHoldingAFileIsKept()
    {
        $this->createFiles(['/img/import/kept.jpg']);

        $this->removeEmptiedDirectories(['/img/import/removed.jpg'], []);

        $this->assertFileExists($this->root . '/img/import/kept.jpg');
    }

    public function testAFolderTheBackupRestoresIsKept()
    {
        $this->createDirectories(['/modules/oldmodule']);

        $this->removeEmptiedDirectories(['/modules/oldmodule/added.php'], ['/modules/oldmodule/oldmodule.php']);

        $this->assertDirectoryExists($this->root . '/modules/oldmodule');
    }

    public function testAnEmptyFolderNoRemovedFileWasInIsKept()
    {
        $this->createDirectories(['/upload', '/modules/newmodule']);

        $this->removeEmptiedDirectories(['/modules/newmodule/newmodule.php'], []);

        $this->assertDirectoryExists($this->root . '/upload');
        $this->assertDirectoryNotExists($this->root . '/modules/newmodule');
    }

    /**
     * @param string[] $removedFiles
     * @param string[] $archivedFiles
     */
    private function removeEmptiedDirectories(array $removedFiles, array $archivedFiles)
    {
        $adapter = new FilesystemAdapter(
            $this->filesystem,
            $this->createMock(FileFilter::class),
            $this->root . '/autoupgrade',
            '/admin',
            $this->root
        );
        $adapter->removeEmptiedDirectories($removedFiles, $archivedFiles);
    }

    /**
     * @param string[] $files
     */
    private function createFiles(array $files)
    {
        foreach ($files as $file) {
            $this->filesystem->dumpFile($this->root . $file, '');
        }
    }

    /**
     * @param string[] $directories
     */
    private function createDirectories(array $directories)
    {
        foreach ($directories as $directory) {
            $this->filesystem->mkdir($this->root . $directory);
        }
    }
}
