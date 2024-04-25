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
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class FilesystemAdapterTest extends TestCase
{
    private $container;
    private $filesystemAdapter;
    /**
     * @var string
     */
    private static $pathToFakeShop;
    /**
     * @var string
     */
    private static $pathToFakeRelease;

    public static function setUpBeforeClass()
    {
        // Create directory of a fake shop & release
        self::$pathToFakeShop = sys_get_temp_dir() . '/fakeShop';
        self::$pathToFakeRelease = sys_get_temp_dir() . '/fakeRelease';
        self::createTreeStructureFromJsonFile(__DIR__ . '/../../fixtures/listOfFiles-ShopExample.json', self::$pathToFakeShop);
        self::createTreeStructureFromJsonFile(__DIR__ . '/../../fixtures/listOfFiles-releaseExample.json', self::$pathToFakeRelease);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->container = new UpgradeContainer('/html', '/html/admin');        // We expect in these tests to NOT update the theme
        $this->container->getUpgradeConfiguration()->set('PS_AUTOUP_UPDATE_DEFAULT_THEME', false);
        $this->filesystemAdapter = $this->container->getFilesystemAdapter();
    }

    public function testListFilesInDirForUpgrade()
    {
        $expected = $this->loadFixtureAndAddPrefixToFilePaths(
            __DIR__ . '/../../fixtures/listOfFiles-upgrade.json',
            self::$pathToFakeRelease
        );

        $actual = $this->filesystemAdapter->listFilesInDir(
            self::$pathToFakeRelease,
            'upgrade',
            true
        );
        // TODO: Should try using assertEqualsCanonicalizing after upgrade of PHPUnit
        $this->assertEquals([], array_diff($expected, $actual), "There are more files in the expected array than in the actual list: \n" . implode("\n", array_diff($expected, $actual)));
        $this->assertEquals([], array_diff($actual, $expected), "There are more files in the actual array than in the expected list: \n" . implode("\n", array_diff($actual, $expected)));
    }

    public function testListFilesInDirForUpgradeWithTheme()
    {
        $this->container->getUpgradeConfiguration()->set('PS_AUTOUP_UPDATE_DEFAULT_THEME', true);
        $expected = $this->loadFixtureAndAddPrefixToFilePaths(
            __DIR__ . '/../../fixtures/listOfFiles-upgrade-with-theme.json',
            self::$pathToFakeRelease
        );

        $actual = $this->filesystemAdapter->listFilesInDir(
            self::$pathToFakeRelease,
            'upgrade',
            true
        );
        // TODO: Should try using assertEqualsCanonicalizing after upgrade of PHPUnit
        $this->assertEquals([], array_diff($expected, $actual), "There are more files in the expected array than in the actual list: \n" . implode("\n", array_diff($expected, $actual)));
        $this->assertEquals([], array_diff($actual, $expected), "There are more files in the actual array than in the expected list: \n" . implode("\n", array_diff($actual, $expected)));
    }

    public function testListFilesInDirForBackupWithImages()
    {
        $this->container->getUpgradeConfiguration()->set('PS_AUTOUP_KEEP_IMAGES', true);
        $expected = $this->loadFixtureAndAddPrefixToFilePaths(
            __DIR__ . '/../../fixtures/listOfFiles-backup-with-images.json',
            self::$pathToFakeShop
        );

        $actual = $this->filesystemAdapter->listFilesInDir(
            self::$pathToFakeShop,
            'backup'
        );
        // TODO: Should try using assertEqualsCanonicalizing after upgrade of PHPUnit
        $this->assertEquals([], array_diff($expected, $actual), "There are more files in the expected array than in the actual list: \n" . implode("\n", array_diff($expected, $actual)));
        $this->assertEquals([], array_diff($actual, $expected), "There are more files in the actual array than in the expected list: \n" . implode("\n", array_diff($actual, $expected)));
    }

    public function testListFilesInDirForBackupWithoutImages()
    {
        $this->container->getUpgradeConfiguration()->set('PS_AUTOUP_KEEP_IMAGES', false);
        $expected = $this->loadFixtureAndAddPrefixToFilePaths(
            __DIR__ . '/../../fixtures/listOfFiles-backup-without-images.json',
            self::$pathToFakeShop
        );

        $actual = $this->filesystemAdapter->listFilesInDir(
            self::$pathToFakeShop,
            'backup'
        );
        // TODO: Should try using assertEqualsCanonicalizing after upgrade of PHPUnit
        $this->assertEquals([], array_diff($expected, $actual), "There are more files in the expected array than in the actual list: \n" . implode("\n", array_diff($expected, $actual)));
        $this->assertEquals([], array_diff($actual, $expected), "There are more files in the actual array than in the expected list: \n" . implode("\n", array_diff($actual, $expected)));
    }

    public function testListFilesInDirForRestoreWithImages()
    {
        $this->container->getUpgradeConfiguration()->set('PS_AUTOUP_KEEP_IMAGES', true);
        $expected = $this->loadFixtureAndAddPrefixToFilePaths(
            __DIR__ . '/../../fixtures/listOfFiles-restore-with-images.json',
            self::$pathToFakeShop
        );

        $actual = $this->filesystemAdapter->listFilesInDir(
            self::$pathToFakeShop,
            'restore'
        );
        // TODO: Should try using assertEqualsCanonicalizing after upgrade of PHPUnit
        $this->assertEquals([], array_diff($expected, $actual), "There are more files in the expected array than in the actual list: \n" . implode("\n", array_diff($expected, $actual)));
        $this->assertEquals([], array_diff($actual, $expected), "There are more files in the actual array than in the expected list: \n" . implode("\n", array_diff($actual, $expected)));
    }

    public function testListFilesInDirForRestoreWithoutImages()
    {
        $this->container->getUpgradeConfiguration()->set('PS_AUTOUP_KEEP_IMAGES', false);
        $expected = $this->loadFixtureAndAddPrefixToFilePaths(
            __DIR__ . '/../../fixtures/listOfFiles-restore-without-images.json',
            self::$pathToFakeShop
        );

        $actual = $this->filesystemAdapter->listFilesInDir(
            self::$pathToFakeShop,
            'restore'
        );
        // TODO: Should try using assertEqualsCanonicalizing after upgrade of PHPUnit
        $this->assertEquals([], array_diff($expected, $actual), "There are more files in the expected array than in the actual list: \n" . implode("\n", array_diff($expected, $actual)));
        $this->assertEquals([], array_diff($actual, $expected), "There are more files in the actual array than in the expected list: \n" . implode("\n", array_diff($actual, $expected)));
    }

    /**
     * @dataProvider ignoredFilesProvider
     */
    public function testFileIsIgnored($file, $fullpath, $process)
    {
        $this->assertTrue(
            $this->filesystemAdapter->isFileSkipped(
                $file,
                $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . $fullpath,
                $process,
                $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH)
            )
        );
    }

    /**
     * When we list the files to get from a release, we are not working in the current shop.
     * Paths mismatch but we should expect the same results.
     *
     * @dataProvider ignoredFilesProvider
     */
    public function testFileFromReleaseIsIgnored($file, $fullpath, $process)
    {
        $this->assertTrue(
            $this->filesystemAdapter->isFileSkipped(
                $file,
                $this->container->getProperty(UpgradeContainer::LATEST_PATH) . $fullpath,
                $process,
                $this->container->getProperty(UpgradeContainer::LATEST_PATH)
            )
        );
    }

    /**
     * @dataProvider notIgnoredFilesProvider
     */
    public function testFileIsNotIgnored($file, $fullpath, $process)
    {
        $this->assertFalse(
            $this->filesystemAdapter->isFileSkipped(
                $file,
                $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . $fullpath,
                $process,
                $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH)
            )
        );
    }

    public function ignoredFilesProvider()
    {
        return [
            ['.git', '/.git', 'upgrade'],

            ['autoupgrade', '/admin/autoupgrade', 'upgrade'],
            ['autoupgrade', '/admin/autoupgrade', 'restore'],
            ['autoupgrade', '/admin/autoupgrade', 'backup'],

            ['autoupgrade', '/modules/autoupgrade', 'upgrade'],
            ['autoupgrade', '/modules/autoupgrade', 'restore'],
            ['autoupgrade', '/modules/autoupgrade', 'backup'],

            ['classes', '/override/classes', 'upgrade'],
            ['controllers', '/override/controllers', 'upgrade'],
            ['modules', '/override/modules', 'upgrade'],

            ['install', '/install', 'upgrade'],

            ['parameters.yml', '/app/config/parameters.yml', 'upgrade'],

            ['classic', '/themes/classic', 'upgrade'],
        ];
    }

    public function notIgnoredFilesProvider()
    {
        return [
            ['parameters.yml', '/app/config/parameters.yml', 'backup'],
            ['modules', '/some/unrelated/folder/named/modules', 'upgrade'],

            // The case of files is important.
            ['Install', '/Install', 'upgrade'],
            ['DframeInstaller.php', '/vendor/composer/installers/src/Composer/Installers/DframeInstaller.php', 'upgrade'],

            ['doge.txt', '/doge.txt', 'upgrade'],
            ['parameters.yml', '/parameters.yml', 'upgrade'],
            ['parameters.yml.dist', '/app/config/parameters.yml.dist', 'upgrade'],
        ];
    }

    public function testRandomFolderIsNotAPrestashopRelease()
    {
        $this->assertFalse(
            $this->filesystemAdapter->isReleaseValid(__DIR__)
        );
    }

    public function testTempFolderIsAPrestashopRelease()
    {
        // Create temp folder and fill it with the needed files
        $folder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'PSA' . mt_rand(100, 2000);
        $this->fillFolderWithPsAssets($folder);

        $this->assertTrue(
            $this->filesystemAdapter->isReleaseValid($folder)
        );
    }

    /**
     * Weird case where we have a file instead of a folder.
     */
    public function testTempFolderIsNotAPrestashopReleaseAfterChanges()
    {
        // Create temp folder and fill it with the needed files
        $folder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'PSA' . mt_rand(100, 2000);
        $this->fillFolderWithPsAssets($folder);
        rmdir($folder . DIRECTORY_SEPARATOR . 'classes');
        touch($folder . DIRECTORY_SEPARATOR . 'classes');

        $this->assertFalse(
            $this->filesystemAdapter->isReleaseValid($folder)
        );
    }

    protected function fillFolderWithPsAssets($folder)
    {
        mkdir($folder);
        mkdir($folder . DIRECTORY_SEPARATOR . 'classes');
        mkdir($folder . DIRECTORY_SEPARATOR . 'config');
        touch($folder . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'defines.inc.php');
        mkdir($folder . DIRECTORY_SEPARATOR . 'controllers');
        touch($folder . DIRECTORY_SEPARATOR . 'index.php');
    }

    private function loadFixtureAndAddPrefixToFilePaths($fixturePath, $prefixToAdd)
    {
        $fileContents = json_decode(file_get_contents($fixturePath), true);

        return array_map(function ($path) use ($prefixToAdd) {
            return $prefixToAdd . $path;
        }, $fileContents);
    }

    private static function createTreeStructureFromJsonFile($fixturePath, $destinationPath)
    {
        $fileContents = json_decode(file_get_contents($fixturePath), true);

        foreach ($fileContents as $filePath) {
            @mkdir($destinationPath . substr($filePath, 0, strrpos($filePath, '/')), 0777, true);
            touch($destinationPath . $filePath);
        }
    }
}
