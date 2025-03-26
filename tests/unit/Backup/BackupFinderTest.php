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
use PrestaShop\Module\AutoUpgrade\Backup\BackupFinder;
use PrestaShop\Module\AutoUpgrade\Exceptions\BackupException;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Component\Filesystem\Filesystem;

class BackupFinderTest extends TestCase
{
    /** string */
    private static $pathToBackup;
    /** @var Translator */
    private $translator;

    public function setUp()
    {
        $this->translator = $this->createMock(Translator::class);
        $this->translator->method('getLocale')->willReturn('en_US');
    }

    public static function setUpBeforeClass()
    {
        // Create directory of a fake shop & release
        self::$pathToBackup = sys_get_temp_dir() . '/BackupFinderFolder';
        self::createTreeStructureFromJsonFile(__DIR__ . '/../../fixtures/list-of-files/backup-folder.json', self::$pathToBackup);
    }

    public function testListingOfBackups()
    {
        $backupFinder = new BackupFinder($this->translator, self::$pathToBackup);

        $expected = [
            'V1.7.5.0_20240927-115034-19c6d35c',
            'V1.7.5.0_20240927-115350-466afd74',
            'V8.1.0_20240927-122157-25f311e3',
        ];
        $this->assertNotContains('V8.1.8_20241224-094523-wololo12', $backupFinder->getAvailableBackups());
        $this->assertEquals($expected, $backupFinder->getAvailableBackups());
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

    public function testParseBackupMetadata()
    {
        $backupFinder = new BackupFinder($this->translator, self::$pathToBackup);

        $backups = [
            'V1.7.5.0_20240927-115034-19c6d35c',
            'V1.7.5.0_20240927-115350-466afd74',
            'V8.1.0_20240927-122157-25f311e3',
        ];

        $backupsMetadata = array_map(function ($backupName) use ($backupFinder) {
            return $backupFinder->parseBackupMetadata($backupName);
        }, $backups);

        $expected = [
            [
                'timestamp' => 1727437834,
                'datetime' => '9/27/24, 11:50 AM',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115034-19c6d35c',
            ],
            [
                'timestamp' => 1727438030,
                'datetime' => '9/27/24, 11:53 AM',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115350-466afd74',
            ],
            [
                'timestamp' => 1727439717,
                'datetime' => '9/27/24, 12:21 PM',
                'version' => '8.1.0',
                'filename' => 'V8.1.0_20240927-122157-25f311e3',
            ],
        ];

        $this->assertEquals($expected, $backupsMetadata);
    }

    /**
     * @throws BackupException
     */
    public function testParseBackupMetadataError()
    {
        $errorMessage = 'An error occurred while formatting the backup name.';

        $this->translator->method('trans')
            ->willReturn($errorMessage);

        $backupFinder = new BackupFinder($this->translator, self::$pathToBackup);

        $this->expectException(BackupException::class);
        $this->expectExceptionMessage($errorMessage);

        $backupFinder->parseBackupMetadata('V1.7.5.0_toto_20240927-115034-19c6d35c');
    }

    public function testSortBackups()
    {
        $backupFinder = new BackupFinder($this->translator, self::$pathToBackup);

        $actual = [
            [
                'timestamp' => 1727437834,
                'datetime' => '9/27/24, 11:50 AM',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115034-19c6d35c',
            ],
            [
                'timestamp' => 1727438030,
                'datetime' => '9/27/24, 11:53 AM',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115350-466afd74',
            ],
            [
                'timestamp' => 1727439717,
                'datetime' => '9/27/24, 12:21 PM',
                'version' => '8.1.0',
                'filename' => 'V8.1.0_20240927-122157-25f311e3',
            ],
        ];

        $expected = [
            [
                'timestamp' => 1727439717,
                'datetime' => '9/27/24, 12:21 PM',
                'version' => '8.1.0',
                'filename' => 'V8.1.0_20240927-122157-25f311e3',
            ],
            [
                'timestamp' => 1727438030,
                'datetime' => '9/27/24, 11:53 AM',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115350-466afd74',
            ],
            [
                'timestamp' => 1727437834,
                'datetime' => '9/27/24, 11:50 AM',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115034-19c6d35c',
            ],
        ];

        $backupFinder->sortBackupsByNewest($actual);

        $this->assertEquals($expected, $actual);
    }

    public function testGetSortedAndFormattedAvailableBackups()
    {
        $backupFinder = new BackupFinder($this->translator, self::$pathToBackup);

        $expected = [
            [
                'timestamp' => 1727439717,
                'datetime' => '9/27/24, 12:21 PM',
                'version' => '8.1.0',
                'filename' => 'V8.1.0_20240927-122157-25f311e3',
            ],
            [
                'timestamp' => 1727438030,
                'datetime' => '9/27/24, 11:53 AM',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115350-466afd74',
            ],
            [
                'timestamp' => 1727437834,
                'datetime' => '9/27/24, 11:50 AM',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115034-19c6d35c',
            ],
        ];

        $result = $backupFinder->getSortedAndFormatedAvailableBackups();

        $this->assertEquals($expected, $result);
    }
}
