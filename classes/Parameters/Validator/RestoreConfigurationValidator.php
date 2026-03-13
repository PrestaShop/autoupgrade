<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters\Validator;

use PrestaShop\Module\AutoUpgrade\Backup\BackupFinder;
use PrestaShop\Module\AutoUpgrade\Parameters\RestoreConfiguration;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class RestoreConfigurationValidator extends AbstractConfigurationValidator
{
    /**
     * @var BackupFinder
     */
    private $backupFinder;

    public function __construct(Translator $translator, BackupFinder $backupFinder)
    {
        parent::__construct($translator);

        $this->backupFinder = $backupFinder;
    }

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

        if (isset($array[RestoreConfiguration::MAX_SECONDS_PER_BATCH])) {
            $secondPerCallErrors = $this->validateInt($array[RestoreConfiguration::MAX_SECONDS_PER_BATCH], RestoreConfiguration::MAX_SECONDS_PER_BATCH);
            if ($secondPerCallErrors) {
                $errors[] = [
                    'message' => $secondPerCallErrors,
                    'target' => RestoreConfiguration::MAX_SECONDS_PER_BATCH,
                ];
            }
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
