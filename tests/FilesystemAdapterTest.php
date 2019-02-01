<?php
/*
 * 2007-2018 PrestaShop
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
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class FilesystemAdapterTest extends TestCase
{
    private $container;
    private $filesystemAdapter;

    protected function setUp()
    {
        parent::setUp();
        $this->container = new UpgradeContainer('/html', '/html/admin');
        // We expect in these tests to NOT update the theme
        $this->container->getUpgradeConfiguration()->set('PS_AUTOUP_UPDATE_DEFAULT_THEME', false);
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
                $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . $fullpath,
                $process));
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
        return array(
            array('.git', '/.git', 'upgrade'),

            array('autoupgrade', '/admin/autoupgrade', 'upgrade'),
            array('autoupgrade', '/admin/autoupgrade', 'restore'),
            array('autoupgrade', '/admin/autoupgrade', 'backup'),

            array('autoupgrade', '/modules/autoupgrade', 'upgrade'),
            array('autoupgrade', '/modules/autoupgrade', 'restore'),
            array('autoupgrade', '/modules/autoupgrade', 'backup'),

            array('parameters.yml', '/app/config/parameters.yml', 'upgrade'),

            array('classic', '/themes/classic', 'upgrade'),
        );
    }

    public function notIgnoredFilesProvider()
    {
        return array(
            array('parameters.yml', '/app/config/parameters.yml', 'backup'),

            array('doge.txt', '/doge.txt', 'upgrade'),
            array('parameters.yml', '/parameters.yml', 'upgrade'),
            array('parameters.yml.dist', '/app/config/parameters.yml.dist', 'upgrade'),
        );
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
