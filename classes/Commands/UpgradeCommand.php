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
use PrestaShop\Module\AutoUpgrade\ErrorHandler;
use PrestaShop\Module\AutoUpgrade\Log\CliLogger;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Log\StreamedLogger;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\Miscellaneous\UpdateConfig;
use PrestaShop\Module\AutoUpgrade\Task\Runner\AllUpgradeTasks;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpgradeCommand extends Command
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    protected static $defaultName = 'upgrade:start';

    protected function configure(): void
    {
        $this
            ->setDescription('Update your store.')
            ->setHelp(
                'This command allows you to start the update process. ' .
                'Advanced users can refer to the https://devdocs.prestashop-project.org/8/basics/keeping-up-to-date/upgrade-module/upgrade-cli/ for further details on available actions'
            )
            ->addOption('admin-dir', 'ad', InputOption::VALUE_REQUIRED, 'The admin directory name.')
            ->addOption('chain', 'ca', InputOption::VALUE_NONE, 'Allows you to chain update commands.')
            ->addOption('channel', 'ce', InputOption::VALUE_OPTIONAL, 'Selects what upgrade to run (minor, major etc.)')
            ->addOption('config-file-path', 'cf', InputOption::VALUE_OPTIONAL, 'Configuration file location for upgrade.')
            ->addOption('action', 'a', InputOption::VALUE_OPTIONAL, 'Advanced users only. Sets the step you want to start from (Default: UpgradeNow, see https://devdocs.prestashop-project.org/8/basics/keeping-up-to-date/upgrade-module/upgrade-cli/ for other values available)')
            ->addOption('data', 'd', InputOption::VALUE_OPTIONAL, 'Advanced users only. Contains the state of the update process encoded in base64');
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

        $this->logger->debug('Starting the update process.');

        try {
            $prodRootDir = _PS_ROOT_DIR_;
            $this->logger->debug('Production root directory: ' . $prodRootDir);

            $adminDir = $input->getOption('admin-dir');
            $this->logger->debug('Admin directory: ' . $adminDir);
            define('_PS_ADMIN_DIR_', _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . $adminDir);

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
            }

            $controller = new AllUpgradeTasks($upgradeContainer);
            $controller->setOptions([
            'data' => $input->getOption('data'),
            'action' => $input->getOption('action'),
            'channel' => $input->getOption('channel'),
        ]);
            $controller->init();
            $exitCode = $controller->run();
            $this->logger->debug('Controller run completed with exit code: ' . $exitCode);
            $chainMode = $input->getOption('chain');

            if (!$chainMode || $exitCode !== ExitCode::SUCCESS) {
                return $exitCode;
            }

            return $this->chainCommand($output);
        } catch (Exception $e) {
            $this->logger->error('An error occurred during the upgrade process: ' . $e->getMessage());

            return ExitCode::FAIL;
        }
    }

    /**
     * @throws Exception
     */
    private function loadConfiguration(string $configPath, UpgradeContainer $upgradeContainer): int
    {
        $this->logger->debug('Loading configuration from ' . $configPath);
        $configFile = file_get_contents($configPath);
        if (!$configFile) {
            $this->logger->error('Configuration file not found a location ' . $configPath);

            return ExitCode::FAIL;
        }

        $inputData = json_decode($configFile, true);
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

        if (strpos($lastInfo, 'bin/console upgrade:start') !== false) {
            if (preg_match('/--action=(\S+)/', $lastInfo, $actionMatches)) {
                $action = $actionMatches[1];
                $this->logger->debug('Action parameter found: ' . $action);
            }

            if (preg_match('/--data=(\S+)/', $lastInfo, $dataMatches)) {
                $data = $dataMatches[1];
                $this->logger->debug('Data parameter found: ' . $data);
            }
            if (empty($action) || empty($data)) {
                $this->logger->error('The command does not contain the necessary information to continue the upgrade process.');

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
