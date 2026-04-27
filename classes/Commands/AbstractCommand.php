<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Commands;

use Exception;
use InvalidArgumentException;
use PrestaShop\Module\AutoUpgrade\ErrorHandler;
use PrestaShop\Module\AutoUpgrade\Log\CliLogger;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Parameters\Loader\AbstractConfigurationLoader;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
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
    protected function loadConfiguration(AbstractConfigurationLoader $loader, ?string $configPath): int
    {
        $loader->initialize($this->upgradeContainer);

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

            try {
                return $loader->load($configurationData);
            } catch (Exception $e) {
                return ExitCode::FAIL;
            }
        }

        return ExitCode::SUCCESS;
    }

    /**
     * @param InputInterface $input
     * @param array<string, string> $options
     */
    protected function processConsoleInputConfiguration(InputInterface $input, array $options): void
    {
        foreach ($options as $configKey => $optionName) {
            $optionValue = $input->getOption($optionName);
            if ($optionValue !== null) {
                $this->consoleInputConfiguration[$configKey] = $optionValue;
            }
        }
    }
}
