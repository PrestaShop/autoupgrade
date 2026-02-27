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
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleUnzipper;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleUnzipperContext;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use PrestaShop\Module\AutoUpgrade\ZipAction;

class ModuleUnzipperTest extends TestCase
{
    /** @var string */
    private $modulesFolder;

    protected function setUp()
    {
        parent::setUp();

        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }

        $this->modulesFolder = sys_get_temp_dir() . '/ModuleUnzipperTest_modules_' . uniqid();
        mkdir($this->modulesFolder, 0755, true);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->removeDirectory($this->modulesFolder);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function createModuleUnzipper(): ModuleUnzipper
    {
        $translator = $this->createMock(Translator::class);
        $translator->method('trans')->willReturnArgument(0);

        $zipAction = $this->createMock(ZipAction::class);

        return new ModuleUnzipper($translator, $zipAction, $this->modulesFolder);
    }

    public function testUnzipModuleFromDirectoryCopiesModuleIntoItsOwnSubfolder()
    {
        // Simulate a pre-unzipped module directory at e.g. /tmp/.../ps_wololo
        $sourceModuleDir = sys_get_temp_dir() . '/ModuleUnzipperTest_source_' . uniqid() . '/ps_wololo';
        mkdir($sourceModuleDir, 0755, true);
        file_put_contents($sourceModuleDir . '/ps_wololo.php', '<?php // ps_wololo main file');
        file_put_contents($sourceModuleDir . '/config.xml', '<module/>');

        try {
            $context = new ModuleUnzipperContext($sourceModuleDir, 'ps_wololo');
            $unzipper = $this->createModuleUnzipper();
            $unzipper->unzipModule($context);

            // The module files must end up inside modules/ps_wololo/, NOT directly in modules/
            $this->assertDirectoryExists(
                $this->modulesFolder . '/ps_wololo',
                'Module directory modules/ps_wololo/ should have been created'
            );
            $this->assertFileExists(
                $this->modulesFolder . '/ps_wololo/ps_wololo.php',
                'Module main file should be at modules/ps_wololo/ps_wololo.php'
            );
            $this->assertFileExists(
                $this->modulesFolder . '/ps_wololo/config.xml',
                'Module config file should be at modules/ps_wololo/config.xml'
            );

            // The files must NOT have been dumped directly into modules/
            $this->assertFileNotExists(
                $this->modulesFolder . '/ps_wololo.php',
                'Module main file must NOT be placed directly in modules/'
            );
        } finally {
            $this->removeDirectory(dirname($sourceModuleDir));
        }
    }
}
