<?php
/**
 * 2007-2019 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
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
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
use PrestaShop\Module\AutoUpgrade\Module\Disabler;
use Symfony\Component\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

class ModuleDisablerTest extends TestCase
{
    /** @var string */
    private $tempDir;

    /** @var string */
    private $tempModulesDir;

    /** @var string */
    private $tempDisabledModulesDir;

    /** @var Filesystem */
    private $fileSystem;

    protected function setUp()
    {
        parent::setUp();
        $this->fileSystem = new Filesystem();
        $this->tempDir = sys_get_temp_dir() . '/module_disabler';
        $this->tempModulesDir = $this->tempDir . '/modules';
        $this->tempDisabledModulesDir = $this->tempDir . '/disabled_modules';
    }

    private function cleanModules()
    {
        $directories = [
            $this->tempDir,
            $this->tempModulesDir,
            $this->tempDisabledModulesDir,
        ];
        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                $this->fileSystem->remove($directory);
            }
        }
    }

    private function createModules()
    {
        $directories = [
            $this->tempDir,
            $this->tempModulesDir,
            $this->tempDisabledModulesDir,
        ];
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                $this->fileSystem->mkdir($directory, 0755);
            }
        }
        $modules = [
            'module1',
            'module2',
        ];
        foreach ($modules as $module) {
            $modulePath = $this->tempModulesDir . '/' . $module;
            $this->fileSystem->mkdir($modulePath);
            $this->fileSystem->touch($modulePath . '/' . $module . '.php');
        }

        $disabledModules = [
            'module3',
            'module4',
        ];
        foreach ($disabledModules as $module) {
            $modulePath = $this->tempDisabledModulesDir . '/' . $module;
            $this->fileSystem->mkdir($modulePath);
            $this->fileSystem->touch($modulePath . '/' . $module . '.php');
        }
    }

    public function testDisableFromDisk()
    {
        $this->cleanModules();
        $this->createModules();

        $moduleDisabler = new Disabler(
            null,
            $this->fileSystem,
            $this->tempModulesDir,
            $this->tempDisabledModulesDir
        );
        $this->checkModulesPlace(['module1', 'module2'], ['module3', 'module4']);
        $moduleDisabler->disableModuleFromDisk('module1');
        $this->checkModulesPlace(['module2'], ['module1', 'module3', 'module4']);
    }

    /**
     * @depends testDisableFromDisk
     */
    public function testEnableFromDisk()
    {
        $moduleDisabler = new Disabler(
            null,
            $this->fileSystem,
            $this->tempModulesDir,
            $this->tempDisabledModulesDir
        );
        $this->checkModulesPlace(['module2'], ['module1', 'module3', 'module4']);
        $moduleDisabler->enableModuleFromDisk('module4');
        $this->checkModulesPlace(['module2', 'module4'], ['module1', 'module3']);
    }

    /**
     * @param array $modules
     * @param array $disabledModules
     */
    private function checkModulesPlace($modules, $disabledModules)
    {
        foreach ($modules as $module) {
            $modulePath = $this->tempModulesDir . '/' . $module;
            $this->assertTrue($this->fileSystem->exists($modulePath));
            $this->assertTrue(is_dir($modulePath));
            $this->assertTrue($this->fileSystem->exists($modulePath . '/' . $module . '.php'));
            $this->assertFalse($this->fileSystem->exists($this->tempDisabledModulesDir . '/' . $module));
        }

        foreach ($disabledModules as $module) {
            $modulePath = $this->tempDisabledModulesDir . '/' . $module;
            $this->assertTrue($this->fileSystem->exists($modulePath));
            $this->assertTrue(is_dir($modulePath));
            $this->assertTrue($this->fileSystem->exists($modulePath . '/' . $module . '.php'));
            $this->assertFalse($this->fileSystem->exists($this->tempModulesDir . '/' . $module));
        }
    }
}
