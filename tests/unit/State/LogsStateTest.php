<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\State\LogsState;

class LogsStateTest extends TestCase
{
    private $fileConfigurationStorageMock;
    /** @var LogsState */
    private $state;

    protected function setUp(): void
    {
        $this->fileConfigurationStorageMock = $this->createMock(FileStorage::class);
        $this->state = new LogsState($this->fileConfigurationStorageMock);
    }

    public function testExportOfData(): void
    {
        $this->state->setActiveBackupLogFromDateTime('20121212121212');
        $this->state->setActiveRestoreLogFromDateTime('20251225133713');
        $this->state->setActiveUpdateLogFromDateTime('20250101213000');
        $this->state->setTimeZone('Europe/Paris');

        $expected = [
            'activeBackupLogFile' => '20121212121212-backup.txt',
            'activeRestoreLogFile' => '20251225133713-restore.txt',
            'activeUpdateLogFile' => '20250101213000-update.txt',
            'timeZone' => 'Europe/Paris',
        ];

        $this->assertEquals($expected, $this->state->export());
    }

    public function testSetAndGetActiveBackupLogFile(): void
    {
        $timestamp = '20241218';
        $expectedFileName = '20241218-backup.txt';

        $this->fileConfigurationStorageMock
            ->expects($this->once())
            ->method('save')
            ->with([
                'activeBackupLogFile' => $expectedFileName,
                'activeRestoreLogFile' => null,
                'activeUpdateLogFile' => null,
                'timeZone' => null,
            ]);

        $this->state->setActiveBackupLogFromDateTime($timestamp);
        $this->assertEquals($expectedFileName, $this->state->getActiveBackupLogFile());
    }

    public function testSetAndGetActiveRestoreLogFile(): void
    {
        $timestamp = '20241218';
        $expectedFileName = '20241218-restore.txt';

        $this->fileConfigurationStorageMock
            ->expects($this->once())
            ->method('save')
            ->with([
                'activeBackupLogFile' => null,
                'activeRestoreLogFile' => $expectedFileName,
                'activeUpdateLogFile' => null,
                'timeZone' => null,
            ]);

        $this->state->setActiveRestoreLogFromDateTime($timestamp);
        $this->assertEquals($expectedFileName, $this->state->getActiveRestoreLogFile());
    }

    public function testSetAndGetActiveUpdateLogFile(): void
    {
        $timestamp = '20241218';
        $expectedFileName = '20241218-update.txt';

        $this->fileConfigurationStorageMock
            ->expects($this->once())
            ->method('save')
            ->with([
                'activeBackupLogFile' => null,
                'activeRestoreLogFile' => null,
                'activeUpdateLogFile' => $expectedFileName,
                'timeZone' => null,
            ]);

        $this->state->setActiveUpdateLogFromDateTime($timestamp);
        $this->assertEquals($expectedFileName, $this->state->getActiveUpdateLogFile());
    }

    public function testSetAndGetTimezone(): void
    {
        $timezone = 'Europe/Paris';

        $this->fileConfigurationStorageMock
            ->expects($this->once())
            ->method('save')
            ->with([
                'activeBackupLogFile' => null,
                'activeRestoreLogFile' => null,
                'activeUpdateLogFile' => null,
                'timeZone' => $timezone,
            ]);

        $this->state->setTimeZone($timezone);
        $this->assertEquals($timezone, $this->state->getTimeZone());
    }

    public function testLoadState(): void
    {
        $savedState = [
            'activeBackupLogFile' => '20241218-backup.txt',
            'activeRestoreLogFile' => '20241218-restore.txt',
            'activeUpdateLogFile' => '20241218-update.txt',
            'timeZone' => 'Europe/Paris',
        ];

        $this->fileConfigurationStorageMock
            ->expects($this->once())
            ->method('load')
            ->with(UpgradeFileNames::STATE_LOGS_FILENAME)
            ->willReturn($savedState);

        $this->state->load();

        $this->assertEquals('20241218-backup.txt', $this->state->getActiveBackupLogFile());
        $this->assertEquals('20241218-restore.txt', $this->state->getActiveRestoreLogFile());
        $this->assertEquals('20241218-update.txt', $this->state->getActiveUpdateLogFile());
        $this->assertEquals('Europe/Paris', $this->state->getTimeZone());
    }

    public function testSaveState(): void
    {
        $savedState = [
            'activeBackupLogFile' => '20241218210000-backup.txt',
            'activeRestoreLogFile' => '20241218210000-restore.txt',
            'activeUpdateLogFile' => '20241218210000-update.txt',
            'timeZone' => 'Europe/Paris',
        ];

        $this->fileConfigurationStorageMock
            ->expects($this->once())
            ->method('load')
            ->with(UpgradeFileNames::STATE_LOGS_FILENAME)
            ->willReturn($savedState);

        $this->state->load();

        $expectedState0 = [
            'activeBackupLogFile' => '20241218210000-backup.txt',
            'activeRestoreLogFile' => '20241218210000-restore.txt',
            'activeUpdateLogFile' => '20241218210000-update.txt',
            'timeZone' => 'Europe/Paris',
        ];

        $expectedState1 = [
            'activeBackupLogFile' => '20241218210000-backup.txt',
            'activeRestoreLogFile' => '20250101122600-restore.txt',
            'activeUpdateLogFile' => '20241218210000-update.txt',
            'timeZone' => 'Europe/Paris',
        ];

        $this->fileConfigurationStorageMock
            ->expects($this->at(0))
            ->method('save')
            ->with($expectedState0, UpgradeFileNames::STATE_LOGS_FILENAME)
            ->willReturn(true);

        $this->fileConfigurationStorageMock
            ->expects($this->at(1))
            ->method('save')
            ->with($expectedState1, UpgradeFileNames::STATE_LOGS_FILENAME)
            ->willReturn(true);

        $this->assertTrue($this->state->save());
        $this->state->setActiveRestoreLogFromDateTime('20250101122600');
    }
}
