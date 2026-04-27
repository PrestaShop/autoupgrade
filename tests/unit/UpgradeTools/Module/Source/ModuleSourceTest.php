<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;

class ModuleSourceTest extends TestCase
{
    public function testClassIsProperlyCreated()
    {
        $moduleName = 'TheModule';
        $newVersion = '9.8.7';
        $path = '/somewhere/only/we/know.zip';
        $unzipable = true;

        $source = new ModuleSource($moduleName, $newVersion, $path, $unzipable);

        $this->assertSame('TheModule', $source->getName());
        $this->assertSame('9.8.7', $source->getNewVersion());
        $this->assertSame('/somewhere/only/we/know.zip', $source->getPath());
        $this->assertSame(true, $source->isZipped());
    }
}
