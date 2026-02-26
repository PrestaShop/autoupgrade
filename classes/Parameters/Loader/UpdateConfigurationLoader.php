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

use Exception;
use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class UpdateConfigurationLoader extends AbstractConfigurationLoader
{
    const TASK_TYPE = TaskType::TASK_TYPE_UPDATE;

    public function initialize(): void
    {
        $updateConfiguration = $this->container->getUpdateConfiguration();
        if (!$updateConfiguration->hasAllTheShopConfiguration()) {
            $this->container->initPrestaShopCore();
            $this->container->getPrestaShopConfiguration()->fillInUpdateConfiguration($updateConfiguration);
        }
        $this->container->getConfigurationStorage()->save($updateConfiguration);
    }

    public function load(array $inputOptions): int
    {
        $config = [];
        $upgradeKeysAssoc = array_fill_keys(UpdateConfiguration::UPGRADE_CONST_KEYS, true);

        // Filter out keys that are not part of the update configuration
        $diff = array_diff_key($inputOptions, $upgradeKeysAssoc);
        foreach ($diff as $key => $configDiff) {
            $this->logger->warning($this->translator->trans("Unknown configuration key '%s', Ignoring.", [$key]));
        }

        foreach (UpdateConfiguration::UPGRADE_CONST_KEYS as $key) {
            if (!isset($inputOptions[$key])) {
                continue;
            }
            // The DISABLE_OVERRIDES variable must only be updated on the database side
            if ($key === UpdateConfiguration::DISABLE_OVERRIDES) {
                UpdateConfiguration::updatePSDisableOverrides((bool) $inputOptions[$key]);
            } else {
                $config[$key] = $inputOptions[$key];
            }
        }

        // Handle local channel logic
        $archiveFilesConfExist = isset($config[UpdateConfiguration::ARCHIVE_XML]) || isset($config[UpdateConfiguration::ARCHIVE_ZIP]);
        if (!isset($config[UpdateConfiguration::CHANNEL]) && $archiveFilesConfExist) {
            $config[UpdateConfiguration::CHANNEL] = UpdateConfiguration::CHANNEL_LOCAL;
        }

        $isLocal = ($config[UpdateConfiguration::CHANNEL] ?? null) === UpdateConfiguration::CHANNEL_LOCAL;

        // Validate configuration
        $error = $this->container->getUpdateConfigurationValidator()->validate($config);

        if ($isLocal && empty($error)) {
            $error = $this->container->getLocalChannelConfigurationValidator()->validate($config);
        }

        if (!empty($error)) {
            $errorMessage = reset($error)['message'];
            $this->logger->error($errorMessage);
            $this->setErrorFlag();

            return ExitCode::FAIL;
        }

        // Handle local archive version extraction
        if ($isLocal) {
            $file = $config[UpdateConfiguration::ARCHIVE_ZIP];
            $fullFilePath = $this->container->getProperty(UpgradeContainer::DOWNLOAD_PATH) . DIRECTORY_SEPARATOR . $file;
            try {
                $config[UpdateConfiguration::ARCHIVE_VERSION_NUM] = $this->container->getPrestashopVersionService()->extractPrestashopVersionFromZip($fullFilePath);
                $this->logger->info($this->translator->trans('Update process will use archive.'));
            } catch (Exception $exception) {
                $errorMessage = $this->translator->trans('We couldn\'t find a PrestaShop version in the .zip file that was uploaded in your local archive. Please try again.');
                $this->logger->error($errorMessage);
                $this->setErrorFlag();

                return ExitCode::FAIL;
            }
        }

        return $this->writeConfig($config);
    }
}
