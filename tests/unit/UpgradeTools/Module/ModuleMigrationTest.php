<?php

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleMigration;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class ModuleMigrationTest extends TestCase
{
    private $translator;
    private $logger;
    private $moduleMigration;

    protected function setUp(): void
    {
        if (!defined('_PS_MODULE_DIR_')) {
            define('_PS_MODULE_DIR_', __DIR__ . '/../../../fixtures/');
        }

        require_once _PS_MODULE_DIR_ . '/Module.php';
        require_once _PS_MODULE_DIR_ . '/mymodule/mymodule.php';

        $this->translator = $this->createMock(Translator::class);
        $this->translator->method('trans')
            ->willReturnCallback(function ($message, $parameters = []) {
                return vsprintf($message, $parameters);
            });

        $this->logger = $this->createMock(Logger::class);
        $this->moduleMigration = new ModuleMigration($this->translator, $this->logger);
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

    public function testlistUpgradeFilesWithSameVersion()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $db_version = '1.0.0';

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);
        $this->assertEquals([], $this->moduleMigration->listUpgradeFiles());
    }

    public function testlistUpgradeFilesWithDifferentVersionButNoUpgradeFile()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '0.0.1';
        $db_version = '0.0.9';

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);
        $this->assertEquals([], $this->moduleMigration->listUpgradeFiles());
    }

    public function testlistUpgradeFilesWithDifferentVersionAndUpgradeFile()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '1.1.0';
        $db_version = '1.0.0';

        $this->moduleMigration->setMigrationContext($mymodule, $db_version);
        $this->assertEquals([
            __DIR__ . '/../../../fixtures/mymodule/upgrade/upgrade-1.0.1.php',
            __DIR__ . '/../../../fixtures/mymodule/upgrade/upgrade-1.1.0.php',
        ], $this->moduleMigration->listUpgradeFiles());
    }
}
