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

namespace PrestaShop\Module\AutoUpgrade\Parameters;

use PrestaShop\Module\AutoUpgrade\Backup\BackupFinder;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class RestoreConfigurationValidator
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var BackupFinder
     */
    private $backupFinder;

    public function __construct(Translator $translator, BackupFinder $backupFinder)
    {
        $this->translator = $translator;
        $this->backupFinder = $backupFinder;
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<array{'message': string, 'target': string}>
     */
    public function validate(array $array = []): array
    {
        $errors = [];

        $backupNameErrors = $this->validateBackupName($array);
        if ($backupNameErrors) {
            $errors[] = [
                'message' => $backupNameErrors,
                'target' => RestoreConfiguration::BACKUP_NAME,
            ];

            return $errors;
        }

        $backupNameExistErrors = $this->validateBackupExist($array[RestoreConfiguration::BACKUP_NAME]);
        if ($backupNameExistErrors) {
            $errors[] = [
                'message' => $backupNameExistErrors,
                'target' => RestoreConfiguration::BACKUP_NAME,
            ];
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $backupConfiguration
     *
     * @return string|null
     */
    private function validateBackupName(array $backupConfiguration): ?string
    {
        if (empty($backupConfiguration[RestoreConfiguration::BACKUP_NAME])) {
            return $this->translator->trans('Invalid configuration, backup name is missing.');
        }

        return null;
    }

    private function validateBackupExist(string $backupName): ?string
    {
        if (!in_array($backupName, $this->backupFinder->getAvailableBackups())) {
            return $this->translator->trans('Invalid configuration, backup %s doesn\'t exist.', [$backupName]);
        }

        return null;
    }
}
