<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\State\BackupState;

class BackupStateTest extends TestCase
{
    private $fileConfigurationStorageMock;
    /** @var BackupState */
    private $state;

    protected function setUp(): void
    {
        $this->fileConfigurationStorageMock = $this->createMock(FileStorage::class);
        $this->state = new BackupState($this->fileConfigurationStorageMock);
    }

    public function testExportOfData(): void
    {
        $this->state->setBackupName('V1.2.3_blablabla-🐶');
        $this->state->setDbStep(3);
        $this->state->setProgressPercentage(20);

        $expected = [
            'backupName' => 'V1.2.3_blablabla-🐶',
            'backupFilesFilename' => 'auto-backupfiles_V1.2.3_blablabla-🐶.zip',
            'backupDbFilename' => 'auto-backupdb_XXXXXX_V1.2.3_blablabla-🐶.sql',
            'backupLoopLimit' => null,
            'backupTable' => null,
            'dbStep' => 3,

            'progressPercentage' => 20,
        ];

        $this->assertEquals($expected, $this->state->export());
    }

    public function testClassReceivesProperty(): void
    {
        $this->state->importFromArray(['backupName' => 'V1.2.3_blablabla-🐶']);
        $exported = $this->state->export();

        $this->assertSame('V1.2.3_blablabla-🐶', $this->state->getBackupName());
        $this->assertSame('V1.2.3_blablabla-🐶', $exported['backupName']);
    }

    public function testClassIgnoresRandomData(): void
    {
        $this->state->importFromArray([
            'wow' => 'epic',
            'backupName' => 'V1.2.3_blablabla-🐶',
        ]);
        $exported = $this->state->export();

        $this->assertFalse(isset($exported['wow']));
        $this->assertSame('V1.2.3_blablabla-🐶', $this->state->getBackupName());
        $this->assertSame('V1.2.3_blablabla-🐶', $exported['backupName']);
    }

    // Tests with encoded data

    public function testClassReceivesPropertyFromEncodedData(): void
    {
        $data = [
            'nextParams' => [
                'backupTable' => 'ps_wololo',
                'backupLoopLimit' => 30,
            ],
        ];
        $encodedData = base64_encode(json_encode($data));
        $this->state->importFromEncodedData($encodedData);
        $exported = $this->state->export();

        $this->assertSame('ps_wololo', $this->state->getBackupTable());
        $this->assertSame('ps_wololo', $exported['backupTable']);
    }

    public function testLoadState(): void
    {
        $savedState = [
            'backupName' => 'V1.2.3_blablabla-🐶',
            'backupFilesFilename' => 'V1.2.3_blablabla-🐶.zip',
            'backupDbFilename' => 'XXXXXX_V1.2.3_blablabla-🐶.sql',
            'backupLoopLimit' => 30,
            'backupTable' => 'ps_wololo',
            'dbStep' => 3,

            'progressPercentage' => 20,
        ];

        $this->fileConfigurationStorageMock
            ->expects($this->once())
            ->method('load')
            ->with(UpgradeFileNames::STATE_BACKUP_FILENAME)
            ->willReturn($savedState);

        $this->state->load();

        $this->assertEquals('V1.2.3_blablabla-🐶', $this->state->getBackupName());
        $this->assertEquals('V1.2.3_blablabla-🐶.zip', $this->state->getBackupFilesFilename());
        $this->assertEquals('XXXXXX_V1.2.3_blablabla-🐶.sql', $this->state->getBackupDbFilename());
        $this->assertEquals(30, $this->state->getBackupLoopLimit());
        $this->assertEquals('ps_wololo', $this->state->getBackupTable());
        $this->assertEquals(3, $this->state->getDbStep());
        $this->assertEquals(20, $this->state->getProgressPercentage());
    }

    public function testUpdateOfBackupName(): void
    {
        $this->state->setBackupName('V8.2.0_thebackup_X');

        $this->assertEquals('auto-backupfiles_V8.2.0_thebackup_X.zip', $this->state->getBackupFilesFilename());
        $this->assertEquals('auto-backupdb_XXXXXX_V8.2.0_thebackup_X.sql', $this->state->getBackupDbFilename());
    }

    public function testProgressionValue(): void
    {
        $this->assertSame(null, $this->state->getProgressPercentage());

        $this->state->setProgressPercentage(0);
        $this->assertSame(0, $this->state->getProgressPercentage());

        $this->state->setProgressPercentage(55);
        $this->assertSame(55, $this->state->getProgressPercentage());

        // Percentage cannot go down, an exception will be thrown
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Updated progress percentage cannot be lower than the currently set one.');

        $this->state->setProgressPercentage(10);
    }
}
