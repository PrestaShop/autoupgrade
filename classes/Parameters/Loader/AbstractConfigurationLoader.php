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
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Parameters\ConfigurationStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\Validator\AbstractConfigurationValidator;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

abstract class AbstractConfigurationLoader
{
    /** @var Logger */
    protected $logger;

    /** @var Translator */
    protected $translator;

    /** @var ConfigurationStorage */
    protected $configurationStorage;

    /** @var array<string, AbstractConfigurationValidator> */
    protected $configurationValidators;

    public function __construct(Logger $logger, Translator $translator, ConfigurationStorage $configurationStorage, array $configurationValidators)
    {
        $this->logger = $logger;
        $this->translator = $translator;
        $this->configurationStorage = $configurationStorage;
        $this->configurationValidators = $configurationValidators;
    }

    /**
     * @throws Exception
     */
    public function initialize(UpgradeContainer $upgradeContainer): void
    {
        $upgradeContainer->initPrestaShopCore();
    }

    /**
     * @param array<string, mixed> $inputOptions
     *
     * @throws Exception
     */
    abstract public function load(array $inputOptions): int;

    /**
     * @param array<string, mixed> $config
     *
     * @throws Exception
     */
    protected function writeConfig(array $config): int
    {
        $classConfig = null;

        switch (static::class) {
            case UpdateConfigurationLoader::class:
                $classConfig = $this->configurationStorage->getUpdateConfiguration();
                break;
            case BackupConfigurationLoader::class:
                $classConfig = $this->configurationStorage->getBackupConfiguration();
                break;
            case RestoreConfigurationLoader::class:
                $classConfig = $this->configurationStorage->getRestoreConfiguration();
                break;
        }

        $classConfig->merge($config);

        $this->logger->info($this->translator->trans('Configuration successfully updated.'));
        $this->logger->debug('Configuration update: ' . json_encode($classConfig->toArray(), JSON_PRETTY_PRINT));

        if (!$this->configurationStorage->save($classConfig)) {
            $errorMessage = $this->translator->trans('Error on saving configuration');
            $this->logger->error($errorMessage);

            return ExitCode::FAIL;
        }

        return ExitCode::SUCCESS;
    }
}
