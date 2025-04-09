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
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Services\LocalVersionFilesService;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\VersionUtils;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckNewVersionCommand extends AbstractCommand
{
    /** @var string */
    protected static $defaultName = 'update:check-new-version';

    protected function configure(): void
    {
        $this
            ->setDescription('List Prestashop updates available for the store.')
            ->setHelp('This command allows you to list Prestashop updates available for the store.')
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

            $rows = [];

            $currentVersion = $this->upgradeContainer->getProperty(UpgradeContainer::PS_VERSION);

            $localFilesService = $this->upgradeContainer->getLocalVersionFilesService();

            $localVersions = $localFilesService->getLocalVersionsFiles();

            foreach ($localVersions as $localVersion => $files) {
                $updateType = VersionUtils::getUpdateType($currentVersion, $localVersion);
                $zipFiles = 'Zip: ' . implode(', ', $files[LocalVersionFilesService::TYPE_ZIP]) . "\n";
                $xmlFiles = 'Xml: ' . implode(', ', $files[LocalVersionFilesService::TYPE_XML]);
                $rows[] = [$localVersion, UpgradeConfiguration::CHANNEL_LOCAL, $updateType, $zipFiles . $xmlFiles];
            }

            // sort by newest
            usort($rows, function ($a, $b) {
                $versionIndex = 0;

                return $b[$versionIndex] <=> $a[$versionIndex];
            });

            $onlineRelease = $this->upgradeContainer->getUpgrader()->getOnlineDestinationRelease();

            if ($onlineRelease) {
                $destinationVersion = $onlineRelease->getVersion();
                $updateType = VersionUtils::getUpdateType($currentVersion, $destinationVersion);
                array_unshift($rows, [$destinationVersion, UpgradeConfiguration::CHANNEL_ONLINE, $updateType, $onlineRelease->getReleaseNoteUrl()]);
            }

            $table = new Table($output);
            $table
                ->setHeaders(['Version', 'Channel', 'Type', 'Information'])
                ->setRows($rows)
            ;
            $table->render();

            return ExitCode::SUCCESS;
        } catch (Exception $e) {
            $this->logger->error("An error occurred during the check new version process:\n" . $e);
            throw $e;
        }
    }
}
