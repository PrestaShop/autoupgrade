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

namespace PrestaShop\Module\AutoUpgrade\Parameters\Loader;

use PrestaShop\Module\AutoUpgrade\Parameters\BackupConfiguration;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;

class BackupConfigurationLoader extends AbstractConfigurationLoader
{
    public function load(array $inputOptions): int
    {
        $config = [];
        // Filter out keys that are not part of the backup configuration
        $backupKeysAssoc = array_fill_keys(BackupConfiguration::BACKUP_CONST_KEYS, true);

        $diff = array_diff_key($inputOptions, $backupKeysAssoc);
        foreach ($diff as $key => $configDiff) {
            $this->logger->warning($this->translator->trans("Unknown configuration key '%s', Ignoring.", [$key]));
        }

        foreach (BackupConfiguration::BACKUP_CONST_KEYS as $key) {
            if (isset($inputOptions[$key])) {
                $config[$key] = $inputOptions[$key];
            }
        }

        // Validate configuration
        $error = $this->container->getBackupConfigurationValidator()->validate($config);

        if (!empty($error)) {
            $errorMessage = reset($error)['message'];
            $this->logger->error($errorMessage);

            return ExitCode::FAIL;
        }

        return $this->writeConfig($config);
    }
}
