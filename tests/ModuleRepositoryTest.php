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
use Symfony\Component\Filesystem\Filesystem;
use PrestaShop\Module\AutoUpgrade\Module\Repository;
use PrestaShop\Module\AutoUpgrade\Addons\ClientInterface;
use PHPUnit\Framework\TestCase;

class ModuleRepositoryTest extends TestCase
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
        $this->cleanModules();
        $this->createModules();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->cleanModules();
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
            $modulePath = $this->tempModulesDir . DIRECTORY_SEPARATOR . $module;
            $this->fileSystem->mkdir($modulePath);
            $this->fileSystem->touch($modulePath . DIRECTORY_SEPARATOR . $module . '.php');
        }

        $disabledModules = [
            'module3',
            'module4',
        ];
        foreach ($disabledModules as $module) {
            $modulePath = $this->tempDisabledModulesDir . DIRECTORY_SEPARATOR . $module;
            $this->fileSystem->mkdir($modulePath);
            $this->fileSystem->touch($modulePath . DIRECTORY_SEPARATOR . $module . '.php');
        }
    }

    public function testModulesOnDisk()
    {
        $this->cleanModules();
        $this->createModules();

        $moduleRepository = new Repository(
            $this->tempModulesDir,
            $this->tempDisabledModulesDir,
            $this->createAddonsClientMock()
        );
        $modules = $moduleRepository->getModulesOnDisk();
        $this->assertTrue(in_array('module1', $modules));
        $this->assertTrue(in_array('module2', $modules));
        $this->assertFalse(in_array('module3', $modules));
        $this->assertFalse(in_array('module4', $modules));
    }

    public function testDisabledModulesOnDisk()
    {
        $this->cleanModules();
        $this->createModules();

        $moduleRepository = new Repository(
            $this->tempModulesDir,
            $this->tempDisabledModulesDir,
            $this->createAddonsClientMock()
        );
        $disabledModules = $moduleRepository->getDisabledModulesOnDisk();
        $this->assertFalse(in_array('module1', $disabledModules));
        $this->assertFalse(in_array('module2', $disabledModules));
        $this->assertTrue(in_array('module3', $disabledModules));
        $this->assertTrue(in_array('module4', $disabledModules));
    }

    public function testGetModulesFromAddons()
    {
        $addonsClient = $this->createAddonsClientMock([
            1747 => 'skrill',
            1748 => 'paypal',
        ]);
        $moduleRepository = new Repository(
            $this->tempModulesDir,
            $this->tempDisabledModulesDir,
            $addonsClient
        );

        $nativeModules = $moduleRepository->getNativeModulesForVersion('1.7.6');
        $this->assertCount(2, $nativeModules);
        $this->assertTrue(in_array('skrill', $nativeModules));
        $this->assertTrue(in_array('paypal', $nativeModules));
    }

    public function testCustomModules()
    {
        $addonsClient = $this->createAddonsClientMock([
            1747 => 'module1',
            1748 => 'module3',
        ]);

        $moduleRepository = new Repository(
            $this->tempModulesDir,
            $this->tempDisabledModulesDir,
            $addonsClient
        );
        $customModules = $moduleRepository->getCustomModulesOnDisk(['1.6.1']);

        //Only module2 is both in modules and not a native one
        $this->assertFalse(in_array('module1', $customModules));
        $this->assertTrue(in_array('module2', $customModules));
        $this->assertFalse(in_array('module3', $customModules));
        $this->assertFalse(in_array('module4', $customModules));
    }

    /**
     * @param array $modules
     *
     * @return PHPUnit_Framework_MockObject_MockObject|ClientInterface
     */
    private function createAddonsClientMock(array $modules = [])
    {
        $addonsClient = $this
            ->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        if (!empty($modules)) {
            $addonsClient
                ->expects($this->once())
                ->method('request')
                ->willReturn($modules)
            ;
        }

        return $addonsClient;
    }
}
