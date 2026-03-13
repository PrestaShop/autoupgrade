<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleDownloaderContext;

class ModuleDownloaderContextTest extends TestCase
{
    public function testConstructWithCorrectSettings()
    {
        $moduleInfos = [
            'name' => 'mymodule',
            'currentVersion' => '1.2.45',
        ];

        $moduleDownloaderContext = new ModuleDownloaderContext($moduleInfos);

        $this->assertEquals('mymodule', $moduleDownloaderContext->getModuleName());
        $this->assertEquals('1.2.45', $moduleDownloaderContext->getReferenceVersion());
    }

    public function testConstructWithCorrectSettingsAndNotIsLocal()
    {
        $moduleInfos = [
            'name' => 'mymodule',
            'currentVersion' => '1.2.45',
        ];

        $moduleDownloaderContext = new ModuleDownloaderContext($moduleInfos);

        $this->assertEquals('mymodule', $moduleDownloaderContext->getModuleName());
        $this->assertEquals('1.2.45', $moduleDownloaderContext->getReferenceVersion());
    }

    public function testConstructWithEmptyStringModuleName()
    {
        $moduleInfos = [
            'name' => '',
            'currentVersion' => '1.2.45',
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Module name is invalid.');

        new ModuleDownloaderContext($moduleInfos);
    }

    public function testConstructWithEmptyStringModuleID()
    {
        $moduleInfos = [
            'name' => 'mymodule',
            'currentVersion' => '',
        ];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Module version is invalid.');

        new ModuleDownloaderContext($moduleInfos);
    }
}
