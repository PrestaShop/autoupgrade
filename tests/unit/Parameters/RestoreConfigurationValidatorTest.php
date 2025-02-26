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
use PrestaShop\Module\AutoUpgrade\Parameters\RestoreConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\RestoreConfigurationValidator;
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
