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
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FileFilter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;

class FilesystemAdapterTest extends TestCase
{
    /**
     * @var UpgradeContainer
     */
    private $container;
    /**
     * @var FilesystemAdapter
     */
    private $filesystemAdapter;

    protected function setUp()
    {
        parent::setUp();

        $container = $this->getMockBuilder(UpgradeContainer::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([
                '/html', '/html/admin',
            ])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->setMethods([
                'getFileFilter',
            ])
            ->getMock();

        $container->method('getFileFilter')
            ->willReturn(
                $this->getMockBuilder(FileFilter::class)
                    ->enableOriginalConstructor()
                    ->setConstructorArgs([
                        $container->getUpgradeConfiguration(),
                        $container->getProperty(UpgradeContainer::PS_ROOT_PATH),
                        $container->getProperty(UpgradeContainer::WORKSPACE_PATH),
                        '1.7.0.0',
                        '8.0.0',
                    ])
                    ->disableOriginalClone()
                    ->disableArgumentCloning()
                    ->enableProxyingToOriginalMethods()
                    ->getMock()
            );

        $this->container = $container;

        // We expect in these tests to NOT update the theme
        $container->getUpgradeConfiguration()->set('PS_AUTOUP_UPDATE_DEFAULT_THEME', false);
        $this->filesystemAdapter = $this->container->getFilesystemAdapter();
    }

    /**
     * @dataProvider ignoredFilesProvider
     */
    public function testFileIsIgnored($file, $fullpath, $process)
    {
        $this->assertTrue(
            $this->filesystemAdapter->isFileSkipped(
                $file,
                $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . $fullpath, $process
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
            ));
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
                $process));
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

            ['parameters.yml', '/app/config/parameters.yml', 'upgrade'],

            ['classic', '/themes/classic', 'upgrade'],
        ];
    }

    public function notIgnoredFilesProvider()
    {
        return [
            ['parameters.yml', '/app/config/parameters.yml', 'backup'],

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
}
