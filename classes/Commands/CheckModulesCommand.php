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
use PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace\ModuleUpgradeCompatibility;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Services\PhpVersionResolverService;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleCompatibilityChecker;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckModulesCommand extends AbstractCommand
{
    /** @var string */
    protected static $defaultName = 'update:check-modules';

    protected function configure(): void
    {
        $this
            ->setDescription('Check module compatibility and updates.')
            ->addOption('config-file-path', null, InputOption::VALUE_REQUIRED, 'Configuration file location for update.')
            ->addOption(
                'channel',
                null,
                InputOption::VALUE_REQUIRED,
                "Select which update channel to use ('" . UpgradeConfiguration::CHANNEL_LOCAL . "' / '" . UpgradeConfiguration::CHANNEL_ONLINE_RECOMMENDED . "' / '" . UpgradeConfiguration::CHANNEL_ONLINE . "')"
            )
            ->addOption('zip', null, InputOption::VALUE_REQUIRED, 'Sets the archive zip file for a local channel.')
            ->addOption('xml', null, InputOption::VALUE_REQUIRED, 'Sets the archive xml file for a local update.')
            ->setHelp('This command checks the installed modules for compatibility with the target PrestaShop version and lists available updates.')
            ->addArgument(
                'admin-dir',
                InputArgument::REQUIRED,
                'Name of the admin directory.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $this->setupEnvironment($input, $output);
            $this->upgradeContainer->getFileStorage()->clean(UpgradeFileNames::UPDATE_CONFIG_FILENAME);

            $options = [
                UpgradeConfiguration::ARCHIVE_ZIP => 'zip',
                UpgradeConfiguration::ARCHIVE_XML => 'xml',
                UpgradeConfiguration::CHANNEL => 'channel',
            ];
            foreach ($options as $configKey => $optionName) {
                $optionValue = $input->getOption($optionName);
                if ($optionValue !== null) {
                    $this->consoleInputConfiguration[$configKey] = $optionValue;
                }
            }

            $configPath = $input->getOption('config-file-path');
            $exitCode = $this->loadConfiguration($configPath);
            if ($exitCode !== ExitCode::SUCCESS) {
                return $exitCode;
            }
            $this->logger->debug('Configuration loaded successfully.');

            $this->upgradeContainer->initPrestaShopAutoloader();
            $this->upgradeContainer->initPrestaShopCore();
            $config = $this->upgradeContainer->getUpdateConfiguration();
            $channel = $config->getChannelOrDefault();

            if ($channel === UpgradeConfiguration::CHANNEL_ONLINE_RECOMMENDED || $channel === UpgradeConfiguration::CHANNEL_ONLINE) {
                $targetPsVersion = $this->upgradeContainer->getUpgrader()->getOnlineDestinationVersionForChannel($channel);
            } else {
                $zip = $config->getChannelZip();
                if (empty($zip)) {
                    $output->writeln('<error> ✗ Please specify the destination zip file using the zip option..</error>');

                    return ExitCode::FAIL;
                }

                $fullFilePath = $this->upgradeContainer->getProperty(UpgradeContainer::DOWNLOAD_PATH) . DIRECTORY_SEPARATOR . $zip;
                try {
                    $targetPsVersion = $this->upgradeContainer->getPrestashopVersionService()->extractPrestashopVersionFromZip($fullFilePath);
                } catch (Exception $exception) {
                    $output->writeln('<error> ✗ We couldn\'t find a PrestaShop version in the .zip file that was uploaded in your local archive. Please try again.</error>');

                    return ExitCode::FAIL;
                }
            }

            if ($targetPsVersion === null || version_compare($this->upgradeContainer->getCurrentPrestaShopVersion(), $targetPsVersion, '>=')) {
                $output->writeln('<error> ✗ You are already running a PrestaShop version equal to or higher than the latest available for update.</error>');

                return ExitCode::FAIL;
            }

            $modulesInstalled = $this->upgradeContainer->getModuleAdapter()->listModulesPresentInFolderAndInstalled();
            $updateSelfCheck = $this->upgradeContainer->getUpgradeSelfCheck();

            if ($config->isChannelLocal() && $updateSelfCheck->getPhpRequirementsState() === PhpVersionResolverService::COMPATIBILITY_UNKNOWN) {
                $output->writeln('<error> ✗ The compatibility of the modules can\'t be checked with a version of PrestaShop that is not released yet.</error>');

                return ExitCode::FAIL;
            }

            if (!empty($modulesInstalled)) {
                $progressIndicator = new ProgressIndicator($output);
                $output->writeln(sprintf('Prestashop version: %s', $targetPsVersion));

                $progressIndicator->start('Retrieving modules informations, please wait...');
                $mode = $output->isVerbose() ? ModuleCompatibilityChecker::DETAILED_SEARCH : ModuleCompatibilityChecker::COMPLETE_SEARCH;
                $checkResults = $updateSelfCheck->getModulesRequiringAttention($mode);
                $progressIndicator->finish('Retrieving modules informations: Done.');

                if ($output->isVerbose()) {
                    $this->renderDetailedTable($output, $modulesInstalled, $checkResults);
                }
                $this->renderLists($output, $checkResults, $targetPsVersion);
            }

            return ExitCode::SUCCESS;
        } catch (Exception $e) {
            $this->logger->error("An error occurred during the check process:\n" . $e);
            throw $e;
        }
    }

    /**
     * @param array<array{name: string, currentVersion: string}> $modulesInstalled
     * @param array{incompatible_modules: string[], uncertain_modules: string[], compatibility: array<string, ?ModuleUpgradeCompatibility>} $checkResults
     */
    private function renderDetailedTable(OutputInterface $output, array $modulesInstalled, array $checkResults): void
    {
        $output->writeln('Result:');

        $table = new Table($output);
        $table->setHeaders([
            'Module',
            'Compatible',
            'Update available',
            'Local version',
            'Update version available',
        ]);

        foreach ($modulesInstalled as $localModule) {
            $localModuleName = $localModule['name'];
            $localVersion = $localModule['currentVersion'];

            $moduleCompatibility = $checkResults['compatibility'][$localModuleName];

            if (!$moduleCompatibility) {
                $table->addRow([
                    $localModuleName,
                    '<error>✗ Unable to retrieve module information</error>',
                ]);
                continue;
            }

            $table->addRow([
                $localModuleName,
                $moduleCompatibility->isCompatible() ? '✓ Yes' : '✗ No',
                $moduleCompatibility->hasUpdateAvailable() ? '✓ Yes' : '✗ No',
                $localVersion,
                $moduleCompatibility->isCompatible() ? $moduleCompatibility->getCompatibleRelease()->productVersion : '-',
            ]);
        }
        $table->render();
    }

    /**
     * @param array{incompatible_modules: string[], uncertain_modules: string[], compatibility: array<string, ?ModuleUpgradeCompatibility>} $checkResults
     */
    private function renderLists(OutputInterface $output, array $checkResults, string $targetPsVersion): void
    {
        if (!empty($checkResults['incompatible_modules'])) {
            $output->writeln("\t<error>✘</error> " . count($checkResults['incompatible_modules']) . ' incompatible modules');
            $output->writeln("\t  These modules are known to be incompatible with PrestaShop $targetPsVersion. They will be uninstalled before updating the store:");
            foreach ($checkResults['incompatible_modules'] as $module) {
                $output->writeln("\t\t" . $module);
            }
        }

        if (!empty($checkResults['uncertain_modules'])) {
            $output->writeln("\t<warning>⚠</warning> " . count($checkResults['uncertain_modules']) . ' uncertain modules');
            $output->writeln("\t  The compatibility of the following modules with the destination version of PrestaShop cannot be checked. It could be because they are homemade or have been unlisted from the Marketplace. Please review them via the Module Manager:");
            foreach ($checkResults['uncertain_modules'] as $module) {
                $output->writeln("\t\t" . $module);
            }
        }

        if (empty($checkResults['incompatible_modules']) && empty($checkResults['uncertain_modules'])) {
            $output->writeln('<success>✔</success> There is no action needed on the installed modules for this update.');
        }
    }
}
