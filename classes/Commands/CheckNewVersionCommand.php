<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Commands;

use Exception;
use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Exceptions\ProcessException;
use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;
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
     * @throws ProcessException
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
                $rows[] = [$localVersion, UpdateConfiguration::CHANNEL_LOCAL, $updateType, $zipFiles . $xmlFiles];
            }

            // sort by newest
            usort($rows, function ($a, $b) {
                $versionIndex = 0;

                return $b[$versionIndex] <=> $a[$versionIndex];
            });

            $onlineMaxRelease = $this->upgradeContainer->getUpgrader()->getOnlineMaxDestinationRelease();
            $onlineRecommendedRelease = $this->upgradeContainer->getUpgrader()->getOnlineRecommendedDestinationRelease();

            if ($onlineMaxRelease && ($onlineRecommendedRelease === null || $onlineMaxRelease->getVersion() !== $onlineRecommendedRelease->getVersion())) {
                $destinationVersion = $onlineMaxRelease->getVersion();
                $updateType = VersionUtils::getUpdateType($currentVersion, $destinationVersion);
                array_unshift($rows, [$destinationVersion, UpdateConfiguration::CHANNEL_ONLINE, $updateType, $onlineMaxRelease->getReleaseNoteUrl()]);
            }

            if ($onlineRecommendedRelease) {
                $destinationVersion = $onlineRecommendedRelease->getVersion();
                $updateType = VersionUtils::getUpdateType($currentVersion, $destinationVersion);
                array_unshift($rows, [$destinationVersion, UpdateConfiguration::CHANNEL_ONLINE_RECOMMENDED, $updateType, $onlineRecommendedRelease->getReleaseNoteUrl()]);
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
