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
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleMigration;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class ModuleMigrationTest extends TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Logger|(Logger&PHPUnit_Framework_MockObject_MockObject)
     */
    private $logger;

    /**
     * @var ModuleMigration
     */
    private $moduleMigration;

    protected function setUp(): void
    {
        if (!defined('_PS_MODULE_DIR_')) {
            define('_PS_MODULE_DIR_', __DIR__ . '/../../../fixtures/');
        }

        require_once _PS_MODULE_DIR_ . '/Module.php';
        require_once _PS_MODULE_DIR_ . '/mymodule/mymodule.php';

        $translator = $this->createMock(Translator::class);
        $translator->method('trans')
            ->willReturnCallback(function ($message, $parameters = []) {
                return vsprintf($message, $parameters);
            });

        $this->logger = $this->createMock(Logger::class);
        $this->moduleMigration = new ModuleMigration($translator, $this->logger);
    }

    public function testSetMigrationContext()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $db_version = '1.1.0';

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);

        $reflectionModuleMigration = new \ReflectionClass($this->moduleMigration);

        // check if $module_name is correctly set
        $moduleName = $reflectionModuleMigration->getProperty('module_name');
        $moduleName->setAccessible(true);
        $this->assertEquals('mymodule', $moduleName->getValue($this->moduleMigration));

        // check if $upgrade_files_root_path is correctly set
        $upgradeFilesRootPath = $reflectionModuleMigration->getProperty('upgrade_files_root_path');
        $upgradeFilesRootPath->setAccessible(true);
        $this->assertEquals(_PS_MODULE_DIR_ . 'mymodule' . DIRECTORY_SEPARATOR . 'upgrade', $upgradeFilesRootPath->getValue($this->moduleMigration));

        // check if $local_version is correctly set
        $localeVersion = $reflectionModuleMigration->getProperty('local_version');
        $localeVersion->setAccessible(true);
        $this->assertEquals('1.0.0', $localeVersion->getValue($this->moduleMigration));

        // check if $db_version is correctly set
        $dbVersion = $reflectionModuleMigration->getProperty('db_version');
        $dbVersion->setAccessible(true);
        $this->assertEquals('1.1.0', $dbVersion->getValue($this->moduleMigration));
    }

    public function testSetMigrationContextLogWithEmptyDbVersion()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $db_version = null;

        // check we log a notice if we are not db version provided
        $this->logger->expects($this->once())
            ->method('notice')
            ->with('No database version provided for module mymodule, all files for upgrade will be applied.');

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);

        $reflectionModuleMigration = new \ReflectionClass($this->moduleMigration);

        // check if $db_version is correctly set at 0
        $dbVersion = $reflectionModuleMigration->getProperty('db_version');
        $dbVersion->setAccessible(true);
        $this->assertEquals('0', $dbVersion->getValue($this->moduleMigration));
    }

    public function testNeedMigrationWithSameVersion()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $db_version = '1.0.0';

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);
        $this->assertFalse($this->moduleMigration->needMigration());
    }

    public function testNeedMigrationWithDifferentVersionButNoUpgradeFile()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '0.0.1';
        $db_version = '0.0.9';

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);
        $this->assertFalse($this->moduleMigration->needMigration());
    }

    public function testNeedMigrationWithDifferentVersionAndUpgradeFile()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '1.1.0';
        $db_version = '1.0.0';

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);
        $this->assertTrue($this->moduleMigration->needMigration());
    }

    public function testListUpgradeFilesWithSameVersion()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $db_version = '1.0.0';

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);
        $this->assertEquals([], $this->moduleMigration->listUpgradeFiles());
    }

    public function testListUpgradeFilesWithDifferentVersionButNoUpgradeFile()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '0.0.1';
        $db_version = '0.0.9';

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);
        $this->assertEquals([], $this->moduleMigration->listUpgradeFiles());
    }

    public function tesListUpgradeFilesWithDifferentVersionAndUpgradeFiles()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '1.1.0';
        $db_version = '1.0.0';

        $upgradeFiles = $this->moduleMigration->listUpgradeFiles();

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);
        $this->assertEquals([
            __DIR__ . '/../../../fixtures/mymodule/upgrade/upgrade-1.0.1.php',
            __DIR__ . '/../../../fixtures/mymodule/upgrade/upgrade-1.1.php',
        ], $upgradeFiles);
    }

    public function testRunMigrationWithoutMigrationContext()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Module migration context is empty, please run setMigrationContext() first.');

        $this->moduleMigration->runMigration();
    }

    public function testRunMigrationWithoutMigrationFilesSets()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $db_version = '1.0.0';

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Module upgrade files are empty, please run needMigration() first.');

        $this->moduleMigration->runMigration();
    }

    public function testRunMigrationWithXYZDifferentFiles()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '1.1.1';
        $db_version = '0.0.9';

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);
        $this->moduleMigration->needMigration();

        $this->logger->expects($this->exactly(4))
            ->method('notice')
            ->withConsecutive(
                ['(1/4) Applying migration file upgrade-1.php.'],
                ['(2/4) Applying migration file upgrade-1.0.1.php.'],
                ['(3/4) Applying migration file upgrade-1.1.php.'],
                ['(4/4) Applying migration file upgrade-1.1.1.php.']
            );

        $this->moduleMigration->runMigration();
    }

    public function testRunMigrationWithSameInstanceThrowDuplicateMethod()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '1.1.1';
        $db_version = '0.0.9';

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);
        $this->moduleMigration->needMigration();

        $this->expectException(\PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException::class);
        $this->expectExceptionMessage('[WARNING] Method upgrade_module_1 already exists. Migration for module mymodule aborted, you can try again later on the module manager.');

        $this->moduleMigration->runMigration();
    }

    public function testRunMigrationWithBadUpgradeMethodName()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '1.2.0';
        $db_version = '1.1.1';

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);
        $this->moduleMigration->needMigration();

        $this->expectException(\PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException::class);
        $this->expectExceptionMessage('[WARNING] Method upgrade_module_1_2_0 does not exist.');

        $this->moduleMigration->runMigration();
    }

    public function testRunMigrationWithUpgradeMethodReturnFalse()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '1.2.1';
        $db_version = '1.2.0';

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);
        $this->moduleMigration->needMigration();

        $this->expectException(\PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException::class);
        $this->expectExceptionMessage('[WARNING] The method upgrade_module_1_2_1 encountered an issue during migration.');

        $this->moduleMigration->runMigration();
    }
}
