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

namespace PrestaShop\Module\AutoUpgrade\Task\Miscellaneous;

use Exception;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

/**
 * update configuration after validating the new values.
 */
class UpdateConfig extends AbstractTask
{
    /**
     * Data being passed by CLI entry point
     *
     * @var array<string, mixed>
     */
    protected $cliParameters;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        // nothing next
        $this->next = TaskName::TASK_COMPLETE;

        // Was coming from AdminSelfUpgrade::currentParams before
        $configurationData = $this->getConfigurationData();
        $config = [];

        $upgradeKeysAssoc = array_fill_keys(UpgradeConfiguration::UPGRADE_CONST_KEYS, true);
        $diff = array_diff_key($configurationData, $upgradeKeysAssoc);

        foreach ($diff as $key => $configDiff) {
            $this->logger->warning($this->translator->trans("Unknown configuration key '%s', Ignoring.", [$key]));
        }

        foreach (UpgradeConfiguration::UPGRADE_CONST_KEYS as $key) {
            if (!isset($configurationData[$key])) {
                continue;
            }
            // The PS_DISABLE_OVERRIDES variable must only be updated on the database side
            if ($key === UpgradeConfiguration::PS_DISABLE_OVERRIDES) {
                UpgradeConfiguration::updatePSDisableOverrides((bool) $configurationData[$key]);
            } else {
                $config[$key] = $configurationData[$key];
            }
        }

        // If no channel is specified, and there is a configuration relating to archive files, we deduce that the channel is local
        $archiveFilesConfExist = isset($config[UpgradeConfiguration::ARCHIVE_XML]) || isset($config[UpgradeConfiguration::ARCHIVE_ZIP]);
        if (!isset($config[UpgradeConfiguration::CHANNEL]) && $archiveFilesConfExist) {
            $config[UpgradeConfiguration::CHANNEL] = UpgradeConfiguration::CHANNEL_LOCAL;
        }

        $isLocal = $config[UpgradeConfiguration::CHANNEL] === UpgradeConfiguration::CHANNEL_LOCAL;

        $error = $this->container->getConfigurationValidator()->validate($config);

        if ($isLocal && empty($error)) {
            $error = $this->container->getLocalChannelConfigurationValidator()->validate($config);
        }

        if (!empty($error)) {
            $this->setErrorFlag();
            $this->logger->error(reset($error)['message']);

            return ExitCode::FAIL;
        }

        if ($isLocal) {
            $file = $config[UpgradeConfiguration::ARCHIVE_ZIP];
            $fullFilePath = $this->container->getProperty(UpgradeContainer::DOWNLOAD_PATH) . DIRECTORY_SEPARATOR . $file;
            try {
                $config[UpgradeConfiguration::ARCHIVE_VERSION_NUM] = $this->container->getPrestashopVersionService()->extractPrestashopVersionFromZip($fullFilePath);
                $this->logger->info($this->translator->trans('Update process will use archive.'));
            } catch (Exception $exception) {
                $this->setErrorFlag();
                $this->logger->error($this->translator->trans('We couldn\'t find a PrestaShop version in the .zip file that was uploaded in your local archive. Please try again.'));

                return ExitCode::FAIL;
            }
        }

        if (!$this->writeConfig($config)) {
            $this->setErrorFlag();
            $this->logger->error($this->translator->trans('Error on saving configuration'));

            return ExitCode::FAIL;
        }

        return ExitCode::SUCCESS;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function inputCliParameters($parameters): void
    {
        $this->cliParameters = $parameters;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getConfigurationData(): array
    {
        if (null !== $this->cliParameters) {
            return $this->getCLIParams();
        }

        return $this->getRequestParams();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getCLIParams(): array
    {
        if (empty($this->cliParameters)) {
            throw new \RuntimeException('Empty CLI parameters - did CLI entry point failed to provide data?');
        }

        return $this->cliParameters;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getRequestParams(): array
    {
        return empty($_REQUEST['params']) ? $_REQUEST : $_REQUEST['params'];
    }

    /**
     * update module configuration (saved in file UpgradeFiles::configFilename) with $new_config.
     *
     * @param array<string, mixed> $config
     *
     * @return bool true if success
     *
     * @throws Exception
     */
    private function writeConfig(array $config): bool
    {
        $configurationStorage = $this->container->getConfigurationStorage();
        $classConfig = $this->container->getUpdateConfiguration();
        $classConfig->merge($config);

        $this->logger->info($this->translator->trans('Configuration successfully updated.'));

        $this->container->getLogger()->debug('Configuration update: ' . json_encode($classConfig->toArray(), JSON_PRETTY_PRINT));

        return $configurationStorage->save($classConfig);
    }

    public function init(): void
    {
        $this->container->initPrestaShopCore();
    }
}
