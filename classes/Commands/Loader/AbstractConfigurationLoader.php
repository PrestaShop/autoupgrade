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

namespace PrestaShop\Module\AutoUpgrade\Commands\Loader;

use Exception;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

abstract class AbstractConfigurationLoader
{
    /** @var UpgradeContainer */
    protected $container;

    /** @var Logger */
    protected $logger;

    /** @var Translator */
    protected $translator;

    /**
     * @var TaskType::TASK_TYPE_*|null
     */
    const TASK_TYPE = null;

    /**
     * @throws Exception
     */
    public function __construct(UpgradeContainer $container)
    {
        $this->container = $container;
        $this->logger = $this->container->getLogger();
        $this->translator = $this->container->getTranslator();
    }

    /**
     * @throws Exception
     */
    public function initialize(): void
    {
        $this->container->initPrestaShopCore();
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
        $configurationStorage = $this->container->getConfigurationStorage();
        $classConfig = null;

        switch (static::TASK_TYPE) {
            case TaskType::TASK_TYPE_UPDATE:
                $classConfig = $this->container->getUpdateConfiguration();
                break;
            case TaskType::TASK_TYPE_BACKUP:
                $classConfig = $this->container->getBackupConfiguration();
                break;
            case TaskType::TASK_TYPE_RESTORE:
                $classConfig = $this->container->getRestoreConfiguration();
                break;
            default:
                throw new Exception('Unknown task type');
        }

        $classConfig->merge($config);

        $this->logger->info($this->translator->trans('Configuration successfully updated.'));
        $this->logger->debug('Configuration update: ' . json_encode($classConfig->toArray(), JSON_PRETTY_PRINT));

        if (!$configurationStorage->save($classConfig)) {
            $errorMessage = $this->translator->trans('Error on saving configuration');
            $this->logger->error($errorMessage);
            $this->setErrorFlag();

            return ExitCode::FAIL;
        }

        return ExitCode::SUCCESS;
    }

    /**
     * @throws Exception
     */
    protected function setErrorFlag(): void
    {
        if (static::TASK_TYPE) {
            $propertiesType = null;

            switch (static::TASK_TYPE) {
                case TaskType::TASK_TYPE_UPDATE:
                    $propertiesType = Analytics::WITH_UPDATE_PROPERTIES;
                    break;
                case TaskType::TASK_TYPE_BACKUP:
                    $propertiesType = Analytics::WITH_BACKUP_PROPERTIES;
                    break;
                case TaskType::TASK_TYPE_RESTORE:
                    $propertiesType = Analytics::WITH_RESTORE_PROPERTIES;
                    break;
            }

            if ($this->container->getAnalytics()) {
                $this->container->getAnalytics()->track(
                    ucfirst(static::TASK_TYPE) . ' Failed',
                    $propertiesType,
                    ['failing_step' => (new \ReflectionClass($this))->getShortName()]
                );
            }
        }
    }
}
