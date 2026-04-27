<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters\Validator;

use PrestaShop\Module\AutoUpgrade\Parameters\BackupConfiguration;

class BackupConfigurationValidator extends AbstractConfigurationValidator
{
    public function validate(array $array = []): array
    {
        $errors = [];

        foreach ($array as $key => $value) {
            // we let it like this for the future if we need to validate more fields for backup
            switch ($key) {
                case BackupConfiguration::KEEP_IMAGES:
                    $error = $this->validateBool($value, $key);
                    break;
                case BackupConfiguration::MAX_FILES_PER_BATCH:
                case BackupConfiguration::MAX_FILE_SIZE_ALLOWED:
                case BackupConfiguration::MAX_SQL_SIZE_TO_WRITE_PER_BATCH:
                    $error = $this->validateInt($value, $key);
                    break;
                default:
            }

            if (isset($error)) {
                $errors[] = ['message' => $error, 'target' => $key];
            }
        }

        return $errors;
    }
}
