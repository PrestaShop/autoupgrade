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
use PrestaShop\Module\AutoUpgrade\Exceptions\ProcessException;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\SymfonyAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class ModuleAdapterTest extends TestCase
{
    /**
     * @var string
     */
    private $fixturesPath;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Translator|(Translator&PHPUnit_Framework_MockObject_MockObject)
     */
    private $translator;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|SymfonyAdapter|(SymfonyAdapter&PHPUnit_Framework_MockObject_MockObject)
     */
    private $symfonyAdapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixturesPath = __DIR__ . '/../../../fixtures/modules/';

        $this->translator = $this->createMock(Translator::class);
        $this->translator->method('trans')
            ->willReturnCallback(function ($message, $parameters = []) {
                return strtr($message, $parameters);
            });

        $this->symfonyAdapter = $this->createMock(SymfonyAdapter::class);
    }

    public function testListModulesPresentInFolderAndInstalledWithValidModule()
    {
        $installedModules = [
            ['name' => 'mymodule', 'version' => '1.0.0'],
        ];

        $moduleAdapter = $this->createModuleAdapterWithInstalledModules($installedModules);

        $result = $moduleAdapter->listModulesPresentInFolderAndInstalled();

        $this->assertCount(1, $result);
        $this->assertEquals([
            [
                'name' => 'mymodule',
                'currentVersion' => '1.0.0',
            ],
        ], $result);
    }

    public function testListModulesPresentInFolderAndInstalledExcludesAutoupgrade()
    {
        $installedModules = [
            ['name' => 'autoupgrade', 'version' => '5.0.0'],
            ['name' => 'mymodule', 'version' => '1.0.0'],
        ];

        $moduleAdapter = $this->createModuleAdapterWithInstalledModules($installedModules);

        $result = $moduleAdapter->listModulesPresentInFolderAndInstalled();

        $this->assertCount(1, $result);
        $this->assertEquals('mymodule', $result[0]['name']);
    }

    public function testListModulesPresentInFolderAndInstalledSkipsModulesThatAreFiles()
    {
        // Create a file instead of a directory to simulate edge case
        $moduleFilePath = $this->fixturesPath . 'testfile';
        touch($moduleFilePath);

        $installedModules = [
            ['name' => 'testfile', 'version' => '1.0.0'],
            ['name' => 'mymodule', 'version' => '1.0.0'],
        ];

        $moduleAdapter = $this->createModuleAdapterWithInstalledModules($installedModules);

        $result = $moduleAdapter->listModulesPresentInFolderAndInstalled();

        // testfile should be skipped because it's a file, not a directory
        $this->assertCount(1, $result);
        $this->assertEquals('mymodule', $result[0]['name']);

        // Cleanup
        unlink($moduleFilePath);
    }

    public function testListModulesPresentInFolderAndInstalledSkipsModulesWithoutMainPhpFile()
    {
        // Create a module directory without the main PHP file
        $incompleteModulePath = $this->fixturesPath . 'incompletemodule';
        if (!is_dir($incompleteModulePath)) {
            mkdir($incompleteModulePath);
        }

        $installedModules = [
            ['name' => 'incompletemodule', 'version' => '1.0.0'],
            ['name' => 'mymodule', 'version' => '1.0.0'],
        ];

        $moduleAdapter = $this->createModuleAdapterWithInstalledModules($installedModules);

        $result = $moduleAdapter->listModulesPresentInFolderAndInstalled();

        // incompletemodule should be skipped because it lacks incompletemodule.php
        $this->assertCount(1, $result);
        $this->assertEquals('mymodule', $result[0]['name']);

        // Cleanup
        rmdir($incompleteModulePath);
    }

    public function testListModulesPresentInFolderAndInstalledIncludesModulesWithoutConfigXml()
    {
        // This is the key test for the change - mymodule fixture doesn't have config.xml
        // but should still be included
        $configXmlPath = $this->fixturesPath . 'mymodule/config.xml';
        $this->assertFileNotExists($configXmlPath, 'Test fixture should not have config.xml');

        $installedModules = [
            ['name' => 'mymodule', 'version' => '1.0.0'],
        ];

        $moduleAdapter = $this->createModuleAdapterWithInstalledModules($installedModules);

        $result = $moduleAdapter->listModulesPresentInFolderAndInstalled();

        // Module should be included even without config.xml
        $this->assertCount(1, $result);
        $this->assertEquals('mymodule', $result[0]['name']);
        $this->assertEquals('1.0.0', $result[0]['currentVersion']);
    }

    public function testListModulesPresentInFolderAndInstalledWithMultipleModules()
    {
        // Create additional module directories with main PHP files
        $anotherModulePath = $this->fixturesPath . 'anothermodule';
        $thirdModulePath = $this->fixturesPath . 'thirdmodule';

        if (!is_dir($anotherModulePath)) {
            mkdir($anotherModulePath);
        }
        if (!is_dir($thirdModulePath)) {
            mkdir($thirdModulePath);
        }
        touch($anotherModulePath . '/anothermodule.php');
        touch($thirdModulePath . '/thirdmodule.php');

        $installedModules = [
            ['name' => 'mymodule', 'version' => '1.0.0'],
            ['name' => 'anothermodule', 'version' => '2.5.3'],
            ['name' => 'thirdmodule', 'version' => '0.1.0'],
        ];

        $moduleAdapter = $this->createModuleAdapterWithInstalledModules($installedModules);

        $result = $moduleAdapter->listModulesPresentInFolderAndInstalled();

        $this->assertCount(3, $result);

        $moduleNames = array_column($result, 'name');
        $this->assertContains('mymodule', $moduleNames);
        $this->assertContains('anothermodule', $moduleNames);
        $this->assertContains('thirdmodule', $moduleNames);

        // Cleanup
        unlink($anotherModulePath . '/anothermodule.php');
        unlink($thirdModulePath . '/thirdmodule.php');
        rmdir($anotherModulePath);
        rmdir($thirdModulePath);
    }

    public function testListModulesPresentInFolderAndInstalledThrowsExceptionIfDirectoryDoesNotExist()
    {
        $invalidPath = '/this/path/does/not/exist/';

        $installedModules = [
            ['name' => 'mymodule', 'version' => '1.0.0'],
        ];

        $moduleAdapter = new class($this->translator, $invalidPath, $this->symfonyAdapter, $installedModules) extends ModuleAdapter {
            private $installedModules;

            public function __construct($translator, $modulesPath, $symfonyAdapter, $installedModules)
            {
                parent::__construct($translator, $modulesPath, $symfonyAdapter);
                $this->installedModules = $installedModules;
            }

            public function getInstalledVersionOfModules(?array $filterOnModuleNames = null): array
            {
                return $this->installedModules;
            }
        };

        $this->expectException(ProcessException::class);
        $this->expectExceptionMessage('does not exist or is not a directory');

        $moduleAdapter->listModulesPresentInFolderAndInstalled();
    }

    /**
     * Helper method to create a ModuleAdapter with mocked getInstalledVersionOfModules
     *
     * @param array $installedModules
     *
     * @return ModuleAdapter
     */
    private function createModuleAdapterWithInstalledModules(array $installedModules)
    {
        return new class($this->translator, $this->fixturesPath, $this->symfonyAdapter, $installedModules) extends ModuleAdapter {
            private $installedModules;

            public function __construct($translator, $modulesPath, $symfonyAdapter, $installedModules)
            {
                parent::__construct($translator, $modulesPath, $symfonyAdapter);
                $this->installedModules = $installedModules;
            }

            public function getInstalledVersionOfModules(?array $filterOnModuleNames = null): array
            {
                return $this->installedModules;
            }
        };
    }
}
