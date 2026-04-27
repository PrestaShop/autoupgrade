<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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

    /**
     * @param array<string, AbstractConfigurationValidator> $configurationValidators
     */
    public function __construct(Logger $logger, Translator $translator, ConfigurationStorage $configurationStorage, array $configurationValidators)
    {
        $this->logger = $logger;
        $this->translator = $translator;
        $this->configurationStorage = $configurationStorage;
        $this->configurationValidators = $configurationValidators;
    }

    public function initialize(UpgradeContainer $upgradeContainer): void
    {
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
                $classConfig = $this->configurationStorage->loadUpdateConfiguration();
                break;
            case BackupConfigurationLoader::class:
                $classConfig = $this->configurationStorage->loadBackupConfiguration();
                break;
            case RestoreConfigurationLoader::class:
                $classConfig = $this->configurationStorage->loadRestoreConfiguration();
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
