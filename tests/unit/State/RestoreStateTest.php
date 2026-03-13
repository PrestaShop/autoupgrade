<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\State\RestoreState;

class RestoreStateTest extends TestCase
{
    private $fileConfigurationStorageMock;
    /** @var RestoreState */
    private $state;

    protected function setUp(): void
    {
        $this->fileConfigurationStorageMock = $this->createMock(FileStorage::class);
        $this->state = new RestoreState($this->fileConfigurationStorageMock);
    }

    public function testExportOfData(): void
    {
        $this->state->setRestoreName('V1.2.3_blablabla-🐶');
        $this->state->setRestoreFilesFilename('V1.2.3_blablabla-🐶.zip');
        $this->state->setRestoreDbFilenames(['V1.2.3_blablabla-🐶-part1.tgz', 'V1.2.3_blablabla-🐶-part2.tgz']);
        $this->state->setDbStep(3);
        $this->state->setProgressPercentage(20);

        $expected = [
            'restoreName' => 'V1.2.3_blablabla-🐶',
            'restoreFilesFilename' => 'V1.2.3_blablabla-🐶.zip',
            'restoreDbFilenames' => ['V1.2.3_blablabla-🐶-part1.tgz', 'V1.2.3_blablabla-🐶-part2.tgz'],
            'dbStep' => 3,

            'progressPercentage' => '20',
        ];

        $this->assertEquals($expected, $this->state->export());
    }

    public function testClassReceivesProperty(): void
    {
        $this->state->importFromArray(['restoreName' => 'V1.6.3_wololo-L33T']);
        $exported = $this->state->export();

        $this->assertSame('V1.6.3_wololo-L33T', $this->state->getRestoreName());
        $this->assertSame('V1.6.3_wololo-L33T', $exported['restoreName']);
    }

    public function testClassIgnoresRandomData(): void
    {
        $this->state->importFromArray([
            'wow' => 'epic',
            'restoreName' => 'V1.6.3_wololo-L33T',
        ]);
        $exported = $this->state->export();

        $this->assertFalse(isset($exported['wow']));
        $this->assertSame('V1.6.3_wololo-L33T', $this->state->getRestoreName());
        $this->assertSame('V1.6.3_wololo-L33T', $exported['restoreName']);
    }

    // Tests with encoded data

    public function testClassReceivesPropertyFromEncodedData(): void
    {
        $data = [
            'nextParams' => [
                'restoreName' => 'V1.6.3_wololo-L33T',
                'progressPercentage' => 50,
            ],
        ];
        $encodedData = base64_encode(json_encode($data));
        $this->state->importFromEncodedData($encodedData);
        $exported = $this->state->export();

        $this->assertSame(50, $this->state->getProgressPercentage());
        $this->assertSame(50, $exported['progressPercentage']);
    }

    public function testLoadState(): void
    {
        $savedState = [
            'restoreDbFilenames' => ['V1.2.3_blablabla-🐶-part1.tgz', 'V1.2.3_blablabla-🐶-part2.tgz'],
            'dbStep' => 3,
        ];

        $this->fileConfigurationStorageMock
            ->expects($this->once())
            ->method('load')
            ->with(UpgradeFileNames::STATE_RESTORE_FILENAME)
            ->willReturn($savedState);

        $this->state->load();

        $this->assertEquals(['V1.2.3_blablabla-🐶-part1.tgz', 'V1.2.3_blablabla-🐶-part2.tgz'], $this->state->getRestoreDbFilenames());
        $this->assertEquals(3, $this->state->getDbStep());
        $this->assertNull($this->state->getProgressPercentage());
    }

    public function testGetRestoreVersion(): void
    {
        $this->assertSame(
            '1.7.8.11',
            $this->state->setRestoreName('V1.7.8.11_20240604-170048-3ceb32b2')
                ->getRestoreVersion()
        );

        $this->assertSame(
            '8.1.6',
            $this->state->setRestoreName('V8.1.6_20240604-170048-3ceb32b2')
                ->getRestoreVersion()
        );
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
