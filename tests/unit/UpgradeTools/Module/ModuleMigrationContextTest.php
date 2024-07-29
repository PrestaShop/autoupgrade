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
