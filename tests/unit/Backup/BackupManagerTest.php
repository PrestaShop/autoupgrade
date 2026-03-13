<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Backup\BackupFinder;
use PrestaShop\Module\AutoUpgrade\Backup\BackupManager;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Component\Filesystem\Filesystem;

class BackupManagerTest extends TestCase
{
    /** string */
    private static $pathToBackup;
    /** @var Translator */
    private $translator;
    /** @var Analytics */
    private $analytics;

    public function setUp()
    {
        $this->translator = $this->createMock(Translator::class);
        $this->analytics = $this->createMock(Analytics::class);
    }

    public static function setUpBeforeClass()
    {
        // Create directory of a fake shop & release
        self::$pathToBackup = sys_get_temp_dir() . '/BackupManagerFolder';
        self::createTreeStructureFromJsonFile(__DIR__ . '/../../fixtures/list-of-files/backup-folder.json', self::$pathToBackup);
    }

    public function testBackupIsDeleted()
    {
        $backupFinder = new BackupFinder($this->translator, self::$pathToBackup);
        $backupManager = new BackupManager($this->translator, $backupFinder, $this->analytics);

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
