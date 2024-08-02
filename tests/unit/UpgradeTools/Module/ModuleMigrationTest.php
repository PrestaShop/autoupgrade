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
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleMigrationContext;
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

    public function testNeedMigrationWithSameVersion()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $dbVersion = '1.0.0';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->assertFalse($this->moduleMigration->needMigration($moduleMigrationContext));
    }

    public function testNeedMigrationWithDifferentVersionButNoUpgradeFile()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '0.0.1';
        $dbVersion = '0.0.9';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->assertFalse($this->moduleMigration->needMigration($moduleMigrationContext));
    }

    public function testNeedMigrationWithDifferentVersionAndUpgradeFile()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '1.1.0';
        $dbVersion = '1.0.0';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->assertTrue($this->moduleMigration->needMigration($moduleMigrationContext));
    }

    public function testListUpgradeFilesWithNoDbVersion()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $dbVersion = null;

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->logger->expects($this->once())
            ->method('notice')
            ->with('No version present in database for module mymodule, all files for upgrade will be applied.');

        $this->moduleMigration->listUpgradeFiles($moduleMigrationContext);
    }

    public function testListUpgradeFilesWithSameVersion()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $dbVersion = '1.0.0';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->assertEquals([], $this->moduleMigration->listUpgradeFiles($moduleMigrationContext));
    }

    public function testListUpgradeFilesWithDifferentVersionButNoUpgradeFile()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '0.0.1';
        $dbVersion = '0.0.9';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->assertEquals([], $this->moduleMigration->listUpgradeFiles($moduleMigrationContext));
    }

    public function testListUpgradeFilesWithDifferentVersionAndUpgradeFiles()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '1.1.0';
        $dbVersion = '1.0.0';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->assertEquals([
            __DIR__ . '/../../../fixtures/mymodule/upgrade/upgrade-1.0.1.php',
            __DIR__ . '/../../../fixtures/mymodule/upgrade/upgrade-1.1.php',
        ], $this->moduleMigration->listUpgradeFiles($moduleMigrationContext));
    }

    public function testRunMigrationWithoutMigrationFilesSets()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $dbVersion = '1.0.0';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Module upgrade files are empty, please run needMigration() first.');

        $this->moduleMigration->runMigration($moduleMigrationContext);
    }

    public function testRunMigrationWithXYZDifferentFiles()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '1.1.1';
        $dbVersion = '0.0.9';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->moduleMigration->needMigration($moduleMigrationContext);

        $this->logger->expects($this->exactly(4))
            ->method('notice')
            ->withConsecutive(
                ['(1/4) Applying migration file upgrade-1.php.'],
                ['(2/4) Applying migration file upgrade-1.0.1.php.'],
                ['(3/4) Applying migration file upgrade-1.1.php.'],
                ['(4/4) Applying migration file upgrade-1.1.1.php.']
            );

        $this->moduleMigration->runMigration($moduleMigrationContext);
    }

    public function testRunMigrationWithSameInstanceThrowDuplicateMethod()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '1.1.1';
        $dbVersion = '0.0.9';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->moduleMigration->needMigration($moduleMigrationContext);

        $this->expectException(\PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException::class);
        $this->expectExceptionMessage('[WARNING] Method upgrade_module_1 already exists. Migration for module mymodule aborted, you can try again later on the module manager. Module mymodule disabled.');

        $this->moduleMigration->runMigration($moduleMigrationContext);
    }

    public function testRunMigrationWithBadUpgradeMethodName()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '1.2.0';
        $dbVersion = '1.1.1';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->moduleMigration->needMigration($moduleMigrationContext);

        $this->expectException(\PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException::class);
        $this->expectExceptionMessage('[WARNING] Method upgrade_module_1_2_0 does not exist. Module mymodule disabled.');

        $this->moduleMigration->runMigration($moduleMigrationContext);
    }

    public function testRunMigrationWithUpgradeMethodReturnFalse()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '1.2.1';
        $dbVersion = '1.2.0';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->moduleMigration->needMigration($moduleMigrationContext);

        $this->expectException(\PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException::class);
        $this->expectExceptionMessage('[WARNING] Migration failed while running the file upgrade-1.2.1.php. Module mymodule disabled.');

        $this->moduleMigration->runMigration($moduleMigrationContext);
    }

    public function testRunMigrationWithUpgradeMethodThrowError()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '1.2.2';
        $dbVersion = '1.2.1';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->moduleMigration->needMigration($moduleMigrationContext);

        $this->expectException(\PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException::class);
        $this->expectExceptionMessage('[WARNING] Unexpected error when trying to upgrade module mymodule. Module mymodule disabled.');

        $this->moduleMigration->runMigration($moduleMigrationContext);
    }

    public function testSaveVersionInDb()
    {
        $mymodule = new \fixtures\mymodule\mymodule();

        $dbVersion = '1.2.1';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->assertNull($this->moduleMigration->saveVersionInDb($moduleMigrationContext));
    }

    public function testSaveVersionInDbThrowErrorThenFail()
    {
        $mymodule = new \fixtures\mymodule\mymodule();
        $mymodule->version = '';

        $dbVersion = '1.2.1';

        $moduleMigrationContext = new ModuleMigrationContext($mymodule, $dbVersion);

        $this->expectException(\PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException::class);
        $this->expectExceptionMessage('[WARNING] Module mymodule version could not be updated. Database might be unavailable.');

        $this->assertNull($this->moduleMigration->saveVersionInDb($moduleMigrationContext));
    }
}
