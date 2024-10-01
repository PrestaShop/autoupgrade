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
use PrestaShop\Module\AutoUpgrade\Backup\BackupFinder;
use PrestaShop\Module\AutoUpgrade\Backup\BackupManager;
use Symfony\Component\Filesystem\Filesystem;

class BackupManagerTest extends TestCase
{
    /** string */
    private static $pathToBackup;

    public static function setUpBeforeClass()
    {
        // Create directory of a fake shop & release
        self::$pathToBackup = sys_get_temp_dir() . '/BackupManagerFolder';
        self::createTreeStructureFromJsonFile(__DIR__ . '/../../fixtures/list-of-files/backup-folder.json', self::$pathToBackup);
    }

    public function testBackupIsDeleted()
    {
        $backupFinder = new BackupFinder(self::$pathToBackup);
        $backupManager = new BackupManager($backupFinder);

        $expectedBeforeDeletion = [
            'V1.7.5.0_20240927-115034-19c6d35c',
            'V1.7.5.0_20240927-115350-466afd74',
            'V8.1.0_20240927-122157-25f311e3',
        ];
        $expectedAfterDeletion = [
            'V1.7.5.0_20240927-115034-19c6d35c',
            'V8.1.0_20240927-122157-25f311e3',
        ];

        $this->assertEquals($expectedBeforeDeletion, $backupFinder->getAvailableBackups());

        $backupManager->deleteBackup('V1.7.5.0_20240927-115350-466afd74');

        $this->assertEquals($expectedAfterDeletion, $backupFinder->getAvailableBackups());
    }

    public static function tearDownAfterClass()
    {
        (new Filesystem())->remove(self::$pathToBackup);
    }

    private static function createTreeStructureFromJsonFile($fixturePath, $destinationPath)
    {
        $fileContents = json_decode(file_get_contents($fixturePath), true);

        foreach ($fileContents as $filePath) {
            @mkdir($destinationPath . substr($filePath, 0, strrpos($filePath, '/')), 0777, true);
            touch($destinationPath . $filePath);
        }
    }
}
