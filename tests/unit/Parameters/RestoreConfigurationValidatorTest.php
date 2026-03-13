<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Backup\BackupFinder;
use PrestaShop\Module\AutoUpgrade\Parameters\RestoreConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\Validator\RestoreConfigurationValidator;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class RestoreConfigurationValidatorTest extends TestCase
{
    /**
     * @var RestoreConfigurationValidator
     */
    private $validator;

    private $backupFinderMock;

    protected function setUp(): void
    {
        $this->container = new UpgradeContainer('/html', '/html/admin');

        $this->backupFinderMock = $this->createMock(BackupFinder::class);

        $this->validator = new RestoreConfigurationValidator(
            $this->container->getTranslator(),
            $this->backupFinderMock
        );
    }

    public function testValidateReturnsErrorWhenBackupNameIsMissing(): void
    {
        $errors = $this->validator->validate([]);

        $this->assertCount(1, $errors);
        $this->assertSame(['message' => 'Invalid configuration, backup name is missing.', 'target' => RestoreConfiguration::BACKUP_NAME], $errors[0]);
    }

    public function testValidateReturnsErrorWhenBackupDoesNotExist(): void
    {
        $backupName = 'non_existing_backup.zip';

        $this->backupFinderMock
            ->method('getAvailableBackups')
            ->willReturn(['existing_backup.zip']);

        $errors = $this->validator->validate([RestoreConfiguration::BACKUP_NAME => $backupName]);

        $this->assertCount(1, $errors);
        $this->assertSame(['message' => 'Invalid configuration, backup non_existing_backup.zip doesn\'t exist.', 'target' => RestoreConfiguration::BACKUP_NAME], $errors[0]);
    }

    public function testValidateReturnsNoErrorsWhenBackupIsValid(): void
    {
        $backupName = 'existing_backup.zip';

        $this->backupFinderMock
            ->method('getAvailableBackups')
            ->willReturn([$backupName]);

        $errors = $this->validator->validate([RestoreConfiguration::BACKUP_NAME => $backupName]);

        $this->assertCount(0, $errors);
    }
}
