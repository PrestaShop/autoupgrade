<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\AutoUpgrade\Commands;

use Exception;
use PrestaShop\Module\AutoUpgrade\DeveloperDocumentation;
use PrestaShop\Module\AutoUpgrade\ErrorHandler;
use PrestaShop\Module\AutoUpgrade\Log\CliLogger;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Log\StreamedLogger;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\Miscellaneous\UpdateConfig;
use PrestaShop\Module\AutoUpgrade\Task\Runner\AllUpgradeTasks;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    protected static $defaultName = 'update:start';

    protected function configure(): void
    {
        $this
            ->setDescription('Update your store.')
            ->setHelp(
                'This command allows you to start the update process. ' .
                'Advanced users can refer to the ' . DeveloperDocumentation::DEV_DOC_UPGRADE_CLI_URL . ' for further details on available actions'
            )
            ->addArgument('admin-dir', InputArgument::REQUIRED, 'The admin directory name.')
            ->addOption('chain', null, InputOption::VALUE_NONE, 'True by default. Allows you to chain update commands automatically. The command will continue executing subsequent tasks without requiring manual intervention to restart the process.')
            ->addOption('no-chain', null, InputOption::VALUE_NONE, 'Prevents chaining of update commands. The command will execute a task and then stop, logging the next command that needs to be run. You will need to manually restart the process to continue with the next step.')
            ->addOption('channel', null, InputOption::VALUE_REQUIRED, 'Selects what update to run (minor, major etc.)')
            ->addOption('config-file-path', null, InputOption::VALUE_REQUIRED, 'Configuration file location for update.')
            ->addOption('action', null, InputOption::VALUE_REQUIRED, 'Advanced users only. Sets the step you want to start from (Default: UpgradeNow, see ' . DeveloperDocumentation::DEV_DOC_UPGRADE_CLI_URL . ' for other values available)')
            ->addOption('data', null, InputOption::VALUE_REQUIRED, 'Advanced users only. Contains the state of the update process encoded in base64');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->logger = $output->isDecorated() ? new CliLogger($output) : new StreamedLogger();
        if ($output->isQuiet()) {
            $this->logger->setFilter(Logger::ERROR);
        } elseif ($output->isVerbose()) {
            $this->logger->setFilter(Logger::DEBUG);
        } else {
            $this->logger->setFilter(Logger::INFO);
        }

        $chainMode = $input->getOption('chain');
        $noChainMode = $input->getOption('no-chain');

        if ($chainMode && $noChainMode) {
            $this->logger->error('The chain and no-chain options cannot be active at the same time');

            return ExitCode::FAIL;
        }

        $this->logger->debug('Starting the update process.');

        try {
            $prodRootDir = _PS_ROOT_DIR_;
            $this->logger->debug('Production root directory: ' . $prodRootDir);

            $adminDir = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . $input->getArgument('admin-dir');
            $this->logger->debug('Admin directory: ' . $adminDir);
            define('_PS_ADMIN_DIR_', $adminDir);

            $upgradeContainer = new UpgradeContainer($prodRootDir, $adminDir);
            $this->logger->debug('Upgrade container initialized.');

            $this->logger->debug('Logger initialized: ' . get_class($this->logger));

            $upgradeContainer->setLogger($this->logger);
            (new ErrorHandler($this->logger))->enable();
            $this->logger->debug('Error handler enabled.');

            $configPath = $input->getOption('config-file-path');
            if (!empty($configPath)) {
                $exitCode = $this->loadConfiguration($configPath, $upgradeContainer);
                if ($exitCode !== ExitCode::SUCCESS) {
                    return $exitCode;
                }
                $this->logger->debug('Configuration loaded successfully.');
            } else {
                $this->logger->debug('No configuration file defined, use default configuration instead.');
            }

            $controller = new AllUpgradeTasks($upgradeContainer);
            $controller->setOptions([
            'data' => $input->getOption('data'),
            'action' => $input->getOption('action'),
            'channel' => $input->getOption('channel'),
        ]);
            $controller->init();
            $exitCode = $controller->run();
            $this->logger->debug('Process completed with exit code: ' . $exitCode);

            if ($noChainMode || $exitCode !== ExitCode::SUCCESS) {
                return $exitCode;
            }

            return $this->chainCommand($output);
        } catch (Exception $e) {
            $this->logger->error('An error occurred during the update process: ' . $e->getMessage());

            return ExitCode::FAIL;
        }
    }

    /**
     * @throws Exception
     */
    private function loadConfiguration(string $configPath, ?UpgradeContainer $upgradeContainer): int
    {
        $this->logger->debug('Loading configuration from ' . $configPath);
        $configFile = file_get_contents($configPath);
        if (!$configFile) {
            $this->logger->error('Configuration file not found a location ' . $configPath);

            return ExitCode::FAIL;
        }

        $inputData = json_decode($configFile, true);

        if (!$inputData) {
            $this->logger->error('An error occurred during the json decode process, please check the content and syntax of the file content');

            return ExitCode::FAIL;
        }

        $this->logger->debug('Configuration file content: ' . json_encode($inputData));

        $controller = new UpdateConfig($upgradeContainer);
        $controller->inputCliParameters($inputData);
        $controller->init();

        return $controller->run();
    }

    /**
     * @throws Exception
     */
    private function chainCommand(OutputInterface $output): int
    {
        $lastInfo = $this->logger->getLastInfo();

        if (strpos($lastInfo, 'bin/console update:start') !== false) {
            if (preg_match('/--action=(\S+)/', $lastInfo, $actionMatches)) {
                $action = $actionMatches[1];
                $this->logger->debug('Action parameter found: ' . $action);
            }

            if (preg_match('/--data=(\S+)/', $lastInfo, $dataMatches)) {
                $data = $dataMatches[1];
                $this->logger->debug('Data parameter found: ' . $data);
            }
            if (empty($action) || empty($data)) {
                $this->logger->error('The command does not contain the necessary information to continue the update process.');

                return ExitCode::FAIL;
            }
            $new_string = str_replace('INFO - $ ', '', $this->logger->getLastInfo());
            $decorationParam = $output->isDecorated() ? ' --ansi' : '';
            system('php ' . $new_string . $decorationParam, $exitCode);

            return $exitCode;
        }

        return ExitCode::SUCCESS;
    }
}
