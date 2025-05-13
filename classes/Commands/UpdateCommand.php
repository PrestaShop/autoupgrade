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
use PrestaShop\Module\AutoUpgrade\DocumentationLinks;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\Runner\AllUpdateTasks;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\VersionUtils;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends AbstractCommand
{
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
                'Advanced users can refer to the ' . DocumentationLinks::getDevDocUpdateAssistantCliUrl() . ' for further details on available actions'
            )
            ->addArgument('admin-dir', InputArgument::REQUIRED, 'The admin directory name.')
            ->addOption('chain', null, InputOption::VALUE_NONE, 'True by default. Allows you to chain update commands automatically. The command will continue executing subsequent tasks without requiring manual intervention to restart the process.')
            ->addOption('no-chain', null, InputOption::VALUE_NONE, 'Prevents chaining of update commands. The command will execute a task and then stop, logging the next command that needs to be run. You will need to manually restart the process to continue with the next step.')
            ->addOption('channel', null, InputOption::VALUE_REQUIRED, "Selects what update to run ('" . UpgradeConfiguration::CHANNEL_LOCAL . "' / '" . UpgradeConfiguration::CHANNEL_ONLINE . "')")
            ->addOption('zip', null, InputOption::VALUE_REQUIRED, 'Sets the archive zip file for a local update')
            ->addOption('xml', null, InputOption::VALUE_REQUIRED, 'Sets the archive xml file for a local update')
            ->addOption('disable-non-native-modules', null, InputOption::VALUE_REQUIRED, 'Disable all modules installed after the store creation (1 for yes, 0 for no)')
            ->addOption('regenerate-email-templates', null, InputOption::VALUE_REQUIRED, "Regenerate email templates. If you've customized email templates, your changes will be lost if you activate this option (1 for yes, 0 for no)")
            ->addOption('disable-all-overrides', null, InputOption::VALUE_REQUIRED, 'Overriding is a way to replace business behaviors (class files and controller files) to target only one method or as many as you need. This option disables all classes & controllers overrides, allowing you to avoid conflicts during and after updates (1 for yes, 0 for no)')
            ->addOption('config-file-path', null, InputOption::VALUE_REQUIRED, 'Configuration file location for update.')
            ->addOption('action', null, InputOption::VALUE_REQUIRED, 'Advanced users only. Sets the step you want to start from. Only the "' . TaskName::TASK_UPDATE_INITIALIZATION . '" task updates the configuration. (Default: ' . TaskName::TASK_UPDATE_INITIALIZATION . ', see ' . DocumentationLinks::getDevDocUpdateAssistantCliUrl() . ' for other values available)');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $chainMode = $input->getOption('chain');
        $noChainMode = $input->getOption('no-chain');

        if ($chainMode && $noChainMode) {
            throw new InvalidArgumentException('The chain and no-chain options cannot be active at the same time');
        }

        try {
            $this->setupEnvironment($input, $output);

            $action = $input->getOption('action');

            // if we are in the 1st step of the update, we update the configuration
            if ($action === null || $action === TaskName::TASK_UPDATE_INITIALIZATION) {
                $this->logger->debug('Cleaning previous configuration file.');
                $this->upgradeContainer->getFileStorage()->clean(UpgradeFileNames::UPDATE_CONFIG_FILENAME);

                $this->processConsoleInputConfiguration($input);
                $configPath = $input->getOption('config-file-path');
                $exitCode = $this->loadConfiguration($configPath);
                if ($exitCode !== ExitCode::SUCCESS) {
                    return $exitCode;
                }
            } else {
                $updateState = $this->upgradeContainer->getUpdateState();
                // In the special case the user inits the process from a specific task that is not the initialization,
                // we need to initialize the state manually.
                if (!$updateState->isInitialized()) {
                    $updateState->initDefault($this->upgradeContainer->getProperty(UpgradeContainer::PS_VERSION), $this->upgradeContainer->getUpgrader(), $this->upgradeContainer->getUpdateConfiguration());
                }
            }

            $this->logger->debug('Configuration loaded successfully.');
            $this->logger->debug('Starting the update process.');
            $controller = new AllUpdateTasks($this->upgradeContainer);
            $controller->setOptions([
                'action' => $action,
            ]);
            $controller->init();
            $exitCode = $controller->run();

            $this->logger->debug('Process completed with exit code: ' . $exitCode);

            if ($noChainMode || $exitCode !== ExitCode::SUCCESS) {
                if ($noChainMode && $action === TaskName::TASK_UPDATE_COMPLETE) {
                    $this->printPostUpdateChecklist($output);
                }

                return $exitCode;
            }

            return $this->chainCommand($output);
        } catch (Exception $e) {
            $this->logger->error("An error occurred during the update process:\n" . $e);
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    private function chainCommand(OutputInterface $output): int
    {
        $lastInfo = $this->logger->getLastInfo();

        if (!$lastInfo) {
            return ExitCode::SUCCESS;
        }

        if (strpos($lastInfo, self::$defaultName) !== false) {
            if (preg_match('/--action=(\S+)/', $lastInfo, $actionMatches)) {
                $action = $actionMatches[1];
                $this->logger->debug('Action parameter found: ' . $action);
            } else {
                throw new InvalidArgumentException('The command does not contain the necessary information to continue the update process.');
            }

            $newCommand = str_replace('INFO - $ ', '', $lastInfo);
            $decorationParam = $output->isDecorated() ? ' --ansi' : '';
            system('php ' . $newCommand . $decorationParam, $exitCode);

            return $exitCode;
        }

        $this->printPostUpdateChecklist($output);

        return ExitCode::SUCCESS;
    }

    private function processConsoleInputConfiguration(InputInterface $input): void
    {
        $options = [
            UpgradeConfiguration::CHANNEL => 'channel',
            UpgradeConfiguration::ARCHIVE_ZIP => 'zip',
            UpgradeConfiguration::ARCHIVE_XML => 'xml',
            UpgradeConfiguration::PS_AUTOUP_CUSTOM_MOD_DESACT => 'disable-non-native-modules',
            UpgradeConfiguration::PS_AUTOUP_REGEN_EMAIL => 'regenerate-email-templates',
            UpgradeConfiguration::PS_DISABLE_OVERRIDES => 'disable-all-overrides',
        ];
        foreach ($options as $configKey => $optionName) {
            $optionValue = $input->getOption($optionName);
            if ($optionValue !== null) {
                $this->consoleInputConfiguration[$configKey] = $optionValue;
            }
        }
    }

    private function printPostUpdateChecklist(OutputInterface $output): void
    {
        $version = $this->upgradeContainer->getPrestaShopConfiguration()->getPrestaShopVersion();
        $major = VersionUtils::splitPrestaShopVersion($version)['major'];
        $output->writeln('We recommend you to visit the Post-update checklist page in the developer documentation for any further help: ' . DocumentationLinks::getDevDocUpdateAssistantPostUpdateUrl($major));
    }
}
