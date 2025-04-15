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

namespace PrestaShop\Module\AutoUpgrade\Commands;

use Exception;
use InvalidArgumentException;
use PrestaShop\Module\AutoUpgrade\ErrorHandler;
use PrestaShop\Module\AutoUpgrade\Log\CliLogger;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\Miscellaneous\UpdateConfig;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var UpgradeContainer
     */
    protected $upgradeContainer;
    /**
     * @var array<string, int|bool|string>
     */
    protected $consoleInputConfiguration = [];

    /**
     * @throws Exception
     */
    protected function setupEnvironment(InputInterface $input, OutputInterface $output): void
    {
        $this->logger = new CliLogger($output);
        if ($output->isQuiet()) {
            $this->logger->setFilter(Logger::ERROR);
        } elseif ($output->isVerbose()) {
            $this->logger->setFilter(Logger::DEBUG);
        } else {
            $this->logger->setFilter(Logger::INFO);
        }

        $prodRootDir = _PS_ROOT_DIR_;
        $this->logger->debug('Production root directory: ' . $prodRootDir);

        $adminDir = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . $input->getArgument('admin-dir');

        if (!is_dir($adminDir)) {
            throw new InvalidArgumentException(sprintf('Admin directory "%s" does not exist', $adminDir));
        }

        $this->logger->debug('Admin directory: ' . $adminDir);
        define('_PS_ADMIN_DIR_', $adminDir);

        $this->upgradeContainer = new UpgradeContainer($prodRootDir, $adminDir);
        $this->upgradeContainer->loadNecessaryClasses();

        // We need to store the timezone this early because it can be altered by the core initialization later.
        $this->upgradeContainer->getLogsState()->setTimeZone(date_default_timezone_get());

        $this->logger->debug('Update container initialized.');

        $this->logger->debug('Logger initialized: ' . get_class($this->logger));

        $this->logger->setSensitiveData([
            $this->upgradeContainer->getProperty(UpgradeContainer::PS_ADMIN_SUBDIR) => '**admin_folder**',
        ]);
        $this->upgradeContainer->setLogger($this->logger);
        (new ErrorHandler($this->logger))->enable();
        $this->logger->debug('Error handler enabled.');

        $moduleDir = $this->upgradeContainer->getProperty(UpgradeContainer::WORKSPACE_PATH);
        $this->upgradeContainer->getWorkspace()->init($moduleDir);
    }

    /**
     * @throws Exception
     */
    protected function loadConfiguration(?string $configPath): int
    {
        $updateConfiguration = $this->upgradeContainer->getUpdateConfiguration();
        if (!$updateConfiguration->hasAllTheShopConfiguration()) {
            $this->upgradeContainer->initPrestaShopCore();
            $this->upgradeContainer->getPrestaShopConfiguration()->fillInUpdateConfiguration($updateConfiguration);
        }
        $this->upgradeContainer->getConfigurationStorage()->save($updateConfiguration);

        $controller = new UpdateConfig($this->upgradeContainer);

        $configurationData = [];

        if ($configPath !== null) {
            $this->logger->debug('Loading configuration from ' . $configPath);
            $configFile = file_get_contents($configPath);
            if (!$configFile) {
                throw new InvalidArgumentException('Configuration file not found a location ' . $configPath);
            }

            $configurationData = json_decode($configFile, true);

            if (!$configurationData) {
                throw new InvalidArgumentException('An error occurred during the json decode process, please check the content and syntax of the file content');
            }

            $this->logger->debug('Configuration file content: ' . json_encode($configurationData));
        }

        if (!empty($this->consoleInputConfiguration)) {
            $configurationData = array_merge($configurationData, $this->consoleInputConfiguration);
        }

        if (!empty($configurationData)) {
            $this->logger->debug('Following configuration will be used for the process: ' . json_encode($configurationData));

            $controller->inputCliParameters($configurationData);
            $controller->init();

            return $controller->run();
        }

        return ExitCode::SUCCESS;
    }
}
