<?php

namespace PrestaShop\Module\AutoUpgrade\Parameters;

class BackupConfigurationValidator extends AbstractConfigurationValidator
{
    public function validate(array $array = []): array
    {
        $errors = [];

        foreach ($array as $key => $value) {
            // we let it like this for the future if we need to validate more fields for backup
            switch ($key) {
                case BackupConfiguration::PS_AUTOUP_KEEP_IMAGES:
                    $error = $this->validateBool($value, $key);
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
