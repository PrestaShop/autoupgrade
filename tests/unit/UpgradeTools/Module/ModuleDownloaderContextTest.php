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
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleDownloaderContext;

class ModuleDownloaderContextTest extends TestCase
{
    public function testConstructWithCorrectSettings()
    {
        $zipFullPath = 'path/to/zip/my_zip.zip';
        $moduleInfos = [
            'name' => 'mymodule',
            'id' => '1245',
            'is_local' => true,
        ];

        $moduleDownloaderContext = new ModuleDownloaderContext($zipFullPath, $moduleInfos);

        $this->assertEquals($zipFullPath, $moduleDownloaderContext->getZipFullPath());
        $this->assertEquals('mymodule', $moduleDownloaderContext->getModuleName());
        $this->assertEquals(1245, $moduleDownloaderContext->getModuleId());
        $this->assertTrue($moduleDownloaderContext->getModuleIsLocal());
    }

    public function testConstructWithCorrectSettingsAndNotIsLocal()
    {
        $zipFullPath = 'path/to/zip/my_zip.zip';
        $moduleInfos = [
            'name' => 'mymodule',
            'id' => '1245',
        ];

        $moduleDownloaderContext = new ModuleDownloaderContext($zipFullPath, $moduleInfos);

        $this->assertEquals($zipFullPath, $moduleDownloaderContext->getZipFullPath());
        $this->assertEquals('mymodule', $moduleDownloaderContext->getModuleName());
        $this->assertEquals(1245, $moduleDownloaderContext->getModuleId());
        $this->assertFalse($moduleDownloaderContext->getModuleIsLocal());
    }

    public function testConstructWithEmptyStringZipFullPath()
    {
        $zipFullPath = '';
        $moduleInfos = [
            'name' => 'mymodule',
            'id' => '1245',
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Path to zip file is invalid.');

        new ModuleDownloaderContext($zipFullPath, $moduleInfos);
    }

    public function testConstructWithEmptyStringModuleName()
    {
        $zipFullPath = 'path/to/zip/my_zip.zip';
        $moduleInfos = [
            'name' => '',
            'id' => '1245',
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Module name is invalid.');

        new ModuleDownloaderContext($zipFullPath, $moduleInfos);
    }

    public function testConstructWithEmptyStringModuleID()
    {
        $zipFullPath = 'path/to/zip/my_zip.zip';
        $moduleInfos = [
            'name' => 'mymodule',
            'id' => '',
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Module ID is invalid.');

        new ModuleDownloaderContext($zipFullPath, $moduleInfos);
    }
}
