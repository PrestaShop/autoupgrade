<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters\Loader;

use PrestaShop\Module\AutoUpgrade\Parameters\RestoreConfiguration;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;

class RestoreConfigurationLoader extends AbstractConfigurationLoader
{
    public function load(array $inputOptions): int
    {
        $config = [];
        // Filter out keys that are not part of the restore configuration
        $restoreKeysAssoc = array_fill_keys(RestoreConfiguration::RESTORE_CONST_KEYS, true);

        $diff = array_diff_key($inputOptions, $restoreKeysAssoc);
        foreach ($diff as $key => $configDiff) {
            $this->logger->warning($this->translator->trans("Unknown configuration key '%s', Ignoring.", [$key]));
        }

        foreach (RestoreConfiguration::RESTORE_CONST_KEYS as $key) {
            if (isset($inputOptions[$key])) {
                $config[$key] = $inputOptions[$key];
            }
        }

        // Validate configuration
        $error = $this->configurationValidators['restoreConfigurationValidator']->validate($config);

        if (!empty($error)) {
            $errorMessage = reset($error)['message'];
            $this->logger->error($errorMessage);

            return ExitCode::FAIL;
        }

        return $this->writeConfig($config);
    }
}
