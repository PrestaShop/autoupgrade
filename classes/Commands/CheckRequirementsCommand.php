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
use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use PrestaShop\Module\AutoUpgrade\Services\PhpVersionResolverService;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckRequirementsCommand extends AbstractCommand
{
    /** @var string */
    protected static $defaultName = 'update:check';
    const MODULE_CONFIG_DIR = 'autoupgrade';
    /** @var UpgradeSelfCheck */
    private $upgradeSelfCheck;
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
            ->addArgument('admin-dir', InputArgument::REQUIRED, 'The admin directory name.');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->setupContainer($input, $output);
        $this->output = $output;

        $configPath = $input->getOption('config-file-path');
        $exitCode = $this->loadConfiguration($configPath, $this->upgradeContainer);
        if ($exitCode !== ExitCode::SUCCESS) {
            return $exitCode;
        }
        $this->logger->debug('Configuration loaded successfully.');
        $moduleConfigPath = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . self::MODULE_CONFIG_DIR;

        $this->upgradeContainer->initPrestaShopAutoloader();
        $this->upgradeContainer->initPrestaShopCore();

        $distributionApiService = new DistributionApiService();
        $phpVersionResolverService = new PhpVersionResolverService(
            $distributionApiService,
            $this->upgradeContainer->getFileLoader(),
            $this->upgradeContainer->getState()->getOriginVersion()
        );

        $this->upgradeSelfCheck = new UpgradeSelfCheck(
            $this->upgradeContainer->getUpgrader(),
            $this->upgradeContainer->getPrestaShopConfiguration(),
            $this->upgradeContainer->getTranslator(),
            $phpVersionResolverService,
            $this->upgradeContainer->getChecksumCompare(),
            _PS_ROOT_DIR_,
            _PS_ADMIN_DIR_,
            $moduleConfigPath,
            $this->upgradeContainer->getState()->getOriginVersion()
        );

        $output->writeln('Result of prerequisite checks:');
        $this->exitCode = ExitCode::SUCCESS;
        $this->processRequirementWarnings();
        $this->processRequirementErrors();

        if ($this->exitCode === ExitCode::SUCCESS) {
            $output->writeln('<success>✔</success> All prerequisites meet the conditions for an update.');
        }

        return $this->exitCode;
    }

    /**
     * @throws Exception
     */
    private function processRequirementErrors(): void
    {
        foreach ($this->upgradeSelfCheck->getErrors() as $error => $value) {
            switch ($error) {
               case UpgradeSelfCheck::NOT_LOADED_PHP_EXTENSIONS_LIST_NOT_EMPTY:
                   $notLoadedPhpExtensions = $this->upgradeSelfCheck->getNotLoadedPhpExtensions();
                   $this->writeError($this->upgradeSelfCheck->getRequirementWording($error));
                   foreach ($notLoadedPhpExtensions as $notLoadedPhpExtension) {
                       $this->output->writeln('    ' . $notLoadedPhpExtension);
                   }
                   break;
               case UpgradeSelfCheck::NOT_EXIST_PHP_FUNCTIONS_LIST_NOT_EMPTY:
                   $notExistsPhpFunctions = $this->upgradeSelfCheck->getNotExistsPhpFunctions();
                   $this->writeError($this->upgradeSelfCheck->getRequirementWording($error));
                   foreach ($notExistsPhpFunctions as $notExistsPhpFunction) {
                       $this->output->writeln('    ' . $notExistsPhpFunction);
                   }
                   break;
               case UpgradeSelfCheck::NOT_WRITING_DIRECTORY_LIST_NOT_EMPTY:
                   $notWritingDirectories = $this->upgradeSelfCheck->getNotWritingDirectories();
                   $this->writeError($this->upgradeSelfCheck->getRequirementWording($error));
                   foreach ($notWritingDirectories as $notWritingDirectory) {
                       $this->output->writeln('    ' . $notWritingDirectory);
                   }
                   break;
               default:
                   $this->writeError($this->upgradeSelfCheck->getRequirementWording($error));
           }
        }
    }

    private function processRequirementWarnings(): void
    {
        foreach ($this->upgradeSelfCheck->getWarnings() as $warning => $value) {
            switch ($warning) {
                case UpgradeSelfCheck::TEMPERED_FILES_LIST_NOT_EMPTY:
                    $tamperedFiles = $this->upgradeSelfCheck->getTamperedFiles();
                    $this->writeWarning($this->upgradeSelfCheck->getRequirementWording($warning));
                    foreach ($tamperedFiles as $tamperedFile) {
                        $this->output->writeln('    ' . $tamperedFile);
                    }
                    break;
                default:
                    $this->writeWarning($this->upgradeSelfCheck->getRequirementWording($warning));
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
