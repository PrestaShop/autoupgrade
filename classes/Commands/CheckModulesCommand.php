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
use PrestaShop\Module\AutoUpgrade\Exceptions\MarketplaceApiException;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
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
            ->addOption(
                'channel',
                null,
                InputOption::VALUE_REQUIRED,
                "Select which update channel to use ('" . UpgradeConfiguration::CHANNEL_LOCAL . "' / '" . UpgradeConfiguration::CHANNEL_ONLINE_RECOMMENDED . "' / '" . UpgradeConfiguration::CHANNEL_ONLINE . "')"
            )
            ->addOption('zip', null, InputOption::VALUE_REQUIRED, 'Sets the archive zip file for a local channel.')
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

            $zip = $input->getOption('zip');
            $channel = $input->getOption('channel');

            if (!$channel) {
                if ($zip) {
                    $config[UpgradeConfiguration::CHANNEL] = UpgradeConfiguration::CHANNEL_LOCAL;
                } else {
                    $config[UpgradeConfiguration::CHANNEL] = UpgradeConfiguration::CHANNEL_ONLINE_RECOMMENDED;
                }
            } else {
                $config[UpgradeConfiguration::CHANNEL] = $channel;
            }

            if ($zip) {
                $config[UpgradeConfiguration::ARCHIVE_ZIP] = $zip;
            }

            $errors = $this->upgradeContainer->getConfigurationValidator()->validate($config);

            if (!empty($errors)) {
                $output->writeln('<error> ✗ ' . reset($errors)['message'] . '</error>');

                return ExitCode::FAIL;
            }

            $this->upgradeContainer->initPrestaShopAutoloader();
            $this->upgradeContainer->initPrestaShopCore();
            $channel = $config[UpgradeConfiguration::CHANNEL];

            if ($channel === UpgradeConfiguration::CHANNEL_ONLINE_RECOMMENDED || $channel === UpgradeConfiguration::CHANNEL_ONLINE) {
                $targetPsVersion = $this->upgradeContainer->getUpgrader()->getOnlineDestinationVersionForChannel($channel);
            } else {
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
            $marketplaceService = $this->upgradeContainer->getMarketplaceService();

            if (!empty($modulesInstalled)) {
                $progressIndicator = new ProgressIndicator($output);
                $output->writeln(sprintf('Prestashop version: %s', $targetPsVersion));
                $progressIndicator->start('Retrieving modules informations, please wait...');

                $table = new Table($output);
                $table->setHeaders([
                    'Module',
                    'Compatible',
                    'Update available',
                    'Local version',
                    'Update version available',
                ]);

                foreach ($modulesInstalled as $localModule) {
                    $progressIndicator->advance();
                    $localModuleName = $localModule['name'];
                    $localVersion = $localModule['currentVersion'];

                    try {
                        $moduleDetails = $marketplaceService->getModuleDetail($localModuleName);
                    } catch (MarketplaceApiException $e) {
                        $table->addRow([
                        $localModuleName,
                        '<error>✗ Unable to retrieve module information</error>',
                    ]);
                        continue;
                    }

                    $moduleCompatibility = $marketplaceService->findCompatibleModuleUpgrade(
                        $moduleDetails,
                        $targetPsVersion,
                        $localVersion
                    );

                    $table->addRow([
                        $localModuleName,
                        $moduleCompatibility->isCompatible() ? '✓ Yes' : '✗ No',
                        $moduleCompatibility->hasUpdateAvailable() ? '✓ Yes' : '✗ No',
                        $localVersion,
                        $moduleCompatibility->isCompatible() ? $moduleCompatibility->getCompatibleRelease()->productVersion : '-',
                    ]);
                }
                $progressIndicator->finish('Result:');
                $table->render();
            }

            return ExitCode::SUCCESS;
        } catch (Exception $e) {
            $this->logger->error("An error occurred during the check process:\n" . $e);
            throw $e;
        }
    }
}
