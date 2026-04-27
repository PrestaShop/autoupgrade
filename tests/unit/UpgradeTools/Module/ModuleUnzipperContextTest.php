<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace unit\UpgradeTools\Module;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleUnzipperContext;

class ModuleUnzipperContextTest extends TestCase
{
    public function testConstructWithCorrectSettings()
    {
        $zipFullPath = 'path/to/zip/my_zip.zip';
        $moduleName = 'mymodule';

        $moduleUnzipperContext = new ModuleUnzipperContext($zipFullPath, $moduleName);

        $this->assertEquals($zipFullPath, $moduleUnzipperContext->getDestinationFilePath());
        $this->assertEquals($moduleName, $moduleUnzipperContext->getModuleName());
    }

    public function testConstructWithEmptyStringZipFullPath()
    {
        $zipFullPath = '';
        $moduleName = 'mymodule';

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Path to zip file is invalid.');

        new ModuleUnzipperContext($zipFullPath, $moduleName);
    }

    public function testConstructWithEmptyStringModuleName()
    {
        $zipFullPath = 'path/to/zip/my_zip.zip';
        $moduleName = '';

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Module name is invalid.');

        new ModuleUnzipperContext($zipFullPath, $moduleName);
    }
}
