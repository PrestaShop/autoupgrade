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
use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Exceptions\ProcessException;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckRequirementsCommand extends AbstractCommand
{
    /** @var string */
    protected static $defaultName = 'update:check-requirements';
    /** @var OutputInterface */
    private $output;
    /** @var int */
    private $exitCode;

    protected function configure(): void
    {
        $this
            ->setDescription('Check all prerequisites for an update.')
            ->setHelp('This command allows you to check the prerequisites necessary for the proper functioning of an update.')
            ->addOption('config-file-path', null, InputOption::VALUE_REQUIRED, 'Configuration file location for update.')
            ->addOption('channel', null, InputOption::VALUE_REQUIRED, "Selects what update to run ('" . UpgradeConfiguration::CHANNEL_LOCAL . "' / '" . UpgradeConfiguration::CHANNEL_ONLINE_RECOMMENDED . "' / '" . UpgradeConfiguration::CHANNEL_ONLINE . "')")
            ->addOption('zip', null, InputOption::VALUE_REQUIRED, 'Sets the archive zip file for a local update.')
            ->addOption('xml', null, InputOption::VALUE_REQUIRED, 'Sets the archive xml file for a local update.')
            ->addArgument('admin-dir', InputArgument::REQUIRED, 'The admin directory name.');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $this->setupEnvironment($input, $output);
            $this->upgradeContainer->getFileStorage()->clean(UpgradeFileNames::UPDATE_CONFIG_FILENAME);
            $this->output = $output;

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

            $output->writeln('Result of prerequisite checks:');
            $this->exitCode = ExitCode::SUCCESS;
            $this->processRequirementWarnings();
            $this->processRequirementErrors();

            if ($this->exitCode === ExitCode::SUCCESS) {
                $output->writeln('<success>✔</success> All prerequisites meet the conditions for an update.');
            }

            return $this->exitCode;
        } catch (Exception $e) {
            $this->logger->error("An error occurred during the check requirements process:\n" . $e);
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    private function processRequirementErrors(): void
    {
        foreach ($this->upgradeContainer->getUpgradeSelfCheck()->getErrors() as $error => $value) {
            $wording = $this->upgradeContainer->getUpgradeSelfCheck()->getRequirementWording($error);
            $this->writeError($wording['message']);
            if (isset($wording['list'])) {
                foreach ($wording['list'] as $item) {
                    $this->output->writeln('    ' . $item);
                }
            }
        }
    }

    /**
     * @throws DistributionApiException
     * @throws ProcessException
     */
    private function processRequirementWarnings(): void
    {
        foreach ($this->upgradeContainer->getUpgradeSelfCheck()->getWarnings() as $warning => $value) {
            $wording = $this->upgradeContainer->getUpgradeSelfCheck()->getRequirementWording($warning);
            $this->writeWarning($wording['message']);

            switch ($warning) {
                case UpgradeSelfCheck::CORE_TEMPERED_FILES_LIST_NOT_EMPTY:
                    $missingFiles = $this->upgradeContainer->getUpgradeSelfCheck()->getCoreMissingFiles();

                    if (!empty($missingFiles)) {
                        $this->output->writeln("\t" . count($missingFiles) . ' files are missing:');
                        foreach ($missingFiles as $missingFile) {
                            $this->output->writeln("\t\t" . $missingFile);
                        }
                    }

                    $alteredFiles = $this->upgradeContainer->getUpgradeSelfCheck()->getCoreAlteredFiles();

                    if (!empty($alteredFiles)) {
                        $this->output->writeln("\t" . count($alteredFiles) . ' files are altered:');
                        foreach ($alteredFiles as $alteredFile) {
                            $this->output->writeln("\t\t" . $alteredFile);
                        }
                    }
                    break;
                case UpgradeSelfCheck::THEME_TEMPERED_FILES_LIST_NOT_EMPTY:
                    $missingFiles = $this->upgradeContainer->getUpgradeSelfCheck()->getThemeMissingFiles();

                    if (!empty($missingFiles)) {
                        $this->output->writeln("\t" . count($missingFiles) . ' files are missing:');
                        foreach ($missingFiles as $missingFile) {
                            $this->output->writeln("\t\t" . $missingFile);
                        }
                    }

                    $alteredFiles = $this->upgradeContainer->getUpgradeSelfCheck()->getThemeAlteredFiles();

                    if (!empty($alteredFiles)) {
                        $this->output->writeln("\t" . count($alteredFiles) . ' files are altered:');
                        foreach ($alteredFiles as $alteredFile) {
                            $this->output->writeln("\t\t" . $alteredFile);
                        }
                    }
                    break;
                default:
                    if (isset($wording['list'])) {
                        foreach ($wording['list'] as $item) {
                            $this->output->writeln("\t" . $item);
                        }
                    }
            }
        }
    }

    private function writeWarning(string $message): void
    {
        $this->output->writeln('<warning>⚠</warning> ' . $message);
    }

    private function writeError(string $message): void
    {
        $this->exitCode = ExitCode::FAIL;
        $this->output->writeln('<error>✘</error> ' . $message);
    }
}
