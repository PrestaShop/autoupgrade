<?php

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleMigrationContext;

class ModuleMigrationContextTest extends TestCase
{
    protected function setUp(): void
    {
        if (!defined('_PS_MODULE_DIR_')) {
            define('_PS_MODULE_DIR_', __DIR__ . '/../../../fixtures/');
        }

        require_once _PS_MODULE_DIR_ . '/Module.php';
        require_once _PS_MODULE_DIR_ . '/mymodule/mymodule.php';
    }

    public function testConstruct()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $dbVersion = '1.0.0';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->assertEquals($mymodule, $moduleMigrationContext->getModuleInstance());
        $this->assertEquals('mymodule', $moduleMigrationContext->getModuleName());
        $this->assertEquals(_PS_MODULE_DIR_ . 'mymodule' . DIRECTORY_SEPARATOR . 'upgrade', $moduleMigrationContext->getUpgradeFilesRootPath());
        $this->assertEquals('1.0.0', $moduleMigrationContext->getLocalVersion());
        $this->assertEquals($dbVersion, $moduleMigrationContext->getDbVersion());
        $this->assertEquals(null, $moduleMigrationContext->getMigrationFiles());
    }

    public function testSetMigrationFiles()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $dbVersion = '1.0.0';

        $migrationFiles = [
          'mymodule/upgrade/upgrade-1.php',
          'mymodule/upgrade/upgrade-2.php',
        ];

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);
        $moduleMigrationContext->setMigrationFiles($migrationFiles);

        $this->assertEquals($migrationFiles, $moduleMigrationContext->getMigrationFiles());
    }
}
