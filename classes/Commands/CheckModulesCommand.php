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
use PrestaShop\Module\AutoUpgrade\Services\MarketplaceService;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
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
            ->setDescription('Create backup.')
            ->setHelp('This command triggers the creation of the files and database backup.')
            ->addOption('config-file-path', null, InputOption::VALUE_REQUIRED, 'Configuration file location.')
            ->addArgument('admin-dir', InputArgument::REQUIRED, 'The admin directory name.');
    }

    /**
     * @throws DistributionApiException
     * @throws UpgradeException
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $this->setupEnvironment($input, $output);
            $this->upgradeContainer->initPrestaShopAutoloader();
            $this->upgradeContainer->initPrestaShopCore();

            $currentVersion = $this->upgradeContainer->getProperty(UpgradeContainer::PS_VERSION);
            $marketplaceService = new MarketplaceService($this->upgradeContainer->getFileLoader(), $this->upgradeContainer->getProperty(UpgradeContainer::PS_ROOT_PATH));
            $onlineMaxRelease = $this->upgradeContainer->getUpgrader()->getOnlineMaxDestinationRelease()->getVersion();

            $destinationVersionModules = $marketplaceService->listModule($onlineMaxRelease);
            $currentVersionModules = $marketplaceService->listModule($currentVersion);
            $modulesInstalled = $this->upgradeContainer->getModuleAdapter()->listModulesPresentInFolderAndInstalled();
            $marketplaceRemovedModules = [];
            $removedAndInstalled = [];

            if (!empty($currentVersionModules)) {
                foreach ($currentVersionModules as $id => $currentModule) {
                    if (!isset($destinationVersionModules[$id])) {
                        $marketplaceRemovedModules[$id] = [
                            'id' => $id,
                            'name' => $currentModule->getName(),
                            'version' => $currentModule->getVersion(),
                        ];
                    }
                }
            }

            if (!empty($marketplaceRemovedModules) && !empty($modulesInstalled)) {
                foreach ($modulesInstalled as $localModule) {
                    $localName = $localModule['name'];

                    foreach ($marketplaceRemovedModules as $removedModule) {
                        if (strcasecmp($removedModule['name'], $localName) === 0) {
                            $removedAndInstalled[] = [
                            'name' => $localName,
                            'currentVersion' => $localModule['currentVersion'],
                            'latestVersion' => $removedModule['version'],
                        ];
                            break;
                        }
                    }
                }
            }

            if (!empty($removedAndInstalled)) {
                $output->writeln('<info>Modules to uninstall :</info>');
                foreach ($removedAndInstalled as $module) {
                    $output->writeln(sprintf(
                        '- %s (current version %s, latest version : %s)',
                        $module['name'],
                        $module['currentVersion'],
                        $module['latestVersion']
                    ));
                }
            }

            return ExitCode::SUCCESS;
        } catch (Exception $e) {
            $this->logger->error("An error occurred during the check new version process:\n" . $e);
            throw $e;
        }
    }
}
