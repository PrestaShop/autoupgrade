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
                case BackupConfiguration::MAX_FILE_SIZE:
                case BackupConfiguration::MAX_SQL_SIZE_TO_WRITE_PER_CALL:
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
