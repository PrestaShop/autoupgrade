<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters\Loader;

use Exception;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Parameters\ConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\Validator\AbstractConfigurationValidator;
use PrestaShop\Module\AutoUpgrade\Services\PrestashopVersionService;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class UpdateConfigurationLoader extends AbstractConfigurationLoader
{
    /** @var string */
    private $downloadPath;

    /** @var PrestashopVersionService */
    private $prestashopVersionService;

    /**
     * @param array<string, AbstractConfigurationValidator> $configurationValidators
     */
    public function __construct(Logger $logger, Translator $translator, ConfigurationStorage $configurationStorage, array $configurationValidators, string $downloadPath, PrestashopVersionService $prestashopVersionService)
    {
        parent::__construct($logger, $translator, $configurationStorage, $configurationValidators);

        $this->downloadPath = $downloadPath;
        $this->prestashopVersionService = $prestashopVersionService;
    }

    public function initialize(UpgradeContainer $upgradeContainer): void
    {
        $updateConfiguration = $upgradeContainer->getUpdateConfiguration();
        if (!$updateConfiguration->hasAllTheShopConfiguration()) {
            $upgradeContainer->initPrestaShopCore();
            $upgradeContainer->getPrestaShopConfiguration()->fillInUpdateConfiguration($updateConfiguration);
        }
        $upgradeContainer->getConfigurationStorage()->save($updateConfiguration);
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
                $validatedBool = filter_var($inputOptions[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                if ($validatedBool !== null) {
                    UpdateConfiguration::updatePSDisableOverrides($validatedBool);
                }
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
        $error = $this->configurationValidators['updateConfigurationValidator']->validate($config);

        if ($isLocal && empty($error)) {
            $error = $this->configurationValidators['localChannelConfigurationValidator']->validate($config);
        }

        if (!empty($error)) {
            $errorMessage = reset($error)['message'];
            $this->logger->error($errorMessage);

            return ExitCode::FAIL;
        }

        // Handle local archive version extraction
        if ($isLocal) {
            $file = $config[UpdateConfiguration::ARCHIVE_ZIP];
            $fullFilePath = $this->downloadPath . DIRECTORY_SEPARATOR . $file;
            try {
                $config[UpdateConfiguration::ARCHIVE_VERSION_NUM] = $this->prestashopVersionService->extractPrestashopVersionFromZip($fullFilePath);
                $this->logger->info($this->translator->trans('Update process will use archive.'));
            } catch (Exception $exception) {
                $errorMessage = $this->translator->trans('We couldn\'t find a PrestaShop version in the .zip file that was uploaded in your local archive. Please try again.');
                $this->logger->error($errorMessage);

                return ExitCode::FAIL;
            }
        }

        return $this->writeConfig($config);
    }
}
