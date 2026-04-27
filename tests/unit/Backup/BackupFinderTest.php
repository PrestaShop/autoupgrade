<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Backup\BackupFinder;
use PrestaShop\Module\AutoUpgrade\Exceptions\BackupException;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Component\Filesystem\Filesystem;

class BackupFinderTest extends TestCase
{
    /** @var string */
    private static $pathToBackup;
    /** @var string */
    private static $systemTimezone;
    /** @var Translator */
    private $translator;

    public function setUp()
    {
        $this->translator = $this->createMock(Translator::class);
        $this->translator->method('getLocale')->willReturn('en_GB');
    }

    public static function setUpBeforeClass()
    {
        // Create directory of a fake shop & release
        self::$pathToBackup = sys_get_temp_dir() . '/BackupFinderFolder';
        self::createTreeStructureFromJsonFile(__DIR__ . '/../../fixtures/list-of-files/backup-folder.json', self::$pathToBackup);

        self::$systemTimezone = date_default_timezone_get();
        date_default_timezone_set('GMT');
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
        date_default_timezone_set(self::$systemTimezone);
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
                'datetime' => '27/09/2024, 11:50',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115034-19c6d35c',
            ],
            [
                'timestamp' => 1727438030,
                'datetime' => '27/09/2024, 11:53',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115350-466afd74',
            ],
            [
                'timestamp' => 1727439717,
                'datetime' => '27/09/2024, 12:21',
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
                'datetime' => '27/09/2024, 11:50',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115034-19c6d35c',
            ],
            [
                'timestamp' => 1727438030,
                'datetime' => '27/09/2024, 11:53',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115350-466afd74',
            ],
            [
                'timestamp' => 1727439717,
                'datetime' => '27/09/2024, 12:21',
                'version' => '8.1.0',
                'filename' => 'V8.1.0_20240927-122157-25f311e3',
            ],
        ];

        $expected = [
            [
                'timestamp' => 1727439717,
                'datetime' => '27/09/2024, 12:21',
                'version' => '8.1.0',
                'filename' => 'V8.1.0_20240927-122157-25f311e3',
            ],
            [
                'timestamp' => 1727438030,
                'datetime' => '27/09/2024, 11:53',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115350-466afd74',
            ],
            [
                'timestamp' => 1727437834,
                'datetime' => '27/09/2024, 11:50',
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
                'datetime' => '27/09/2024, 12:21',
                'version' => '8.1.0',
                'filename' => 'V8.1.0_20240927-122157-25f311e3',
            ],
            [
                'timestamp' => 1727438030,
                'datetime' => '27/09/2024, 11:53',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115350-466afd74',
            ],
            [
                'timestamp' => 1727437834,
                'datetime' => '27/09/2024, 11:50',
                'version' => '1.7.5.0',
                'filename' => 'V1.7.5.0_20240927-115034-19c6d35c',
            ],
        ];

        $result = $backupFinder->getSortedAndFormatedAvailableBackups();

        $this->assertEquals($expected, $result);
    }
}
