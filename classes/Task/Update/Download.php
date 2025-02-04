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

namespace PrestaShop\Module\AutoUpgrade\Task\Update;

use Exception;
use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

/**
 * Download PrestaShop archive according to the chosen channel.
 */
class Download extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_UPDATE;

    /**
     * @throws Exception
     */
    public function init(): void
    {
        $this->container->initPrestaShopCore();
    }

    /**
     * @throws Exception
     */
    public function run(): int
    {
        if (!\ConfigurationTest::test_fopen() && !\ConfigurationTest::test_curl()) {
            $this->logger->error($this->translator->trans('You need allow_url_fopen or cURL enabled for automatic download to work. You can also manually upload it in filepath %s.', [$this->container->getArchiveFilePath()]));
            $this->next = TaskName::TASK_ERROR;

            return ExitCode::FAIL;
        }

        $this->container->getUpdateState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $this->logger->debug($this->translator->trans('Downloading from %s', [$this->container->getUpgrader()->getOnlineDestinationRelease()->getZipDownloadUrl()]));
        $this->logger->debug($this->translator->trans('File will be saved in %s', [$this->container->getArchiveFilePath()]));

        $releasesDir = $this->container->getProperty(UpgradeContainer::TMP_RELEASES_DIR);
        if ($this->container->getFilesystemAdapter()->clearDirectory($releasesDir)) {
            $this->logger->debug($this->translator->trans('Download directory has been emptied'));
        }

        $report = '';
        $relative_download_path = str_replace(_PS_ROOT_DIR_, '', $releasesDir);
        if (\ConfigurationTest::test_dir($relative_download_path, false, $report)) {
            $this->downloadArchive();
        } else {
            $this->logger->error($this->translator->trans('Download directory %s is not writable.', [$this->container->getProperty(UpgradeContainer::TMP_RELEASES_DIR)]));
            $this->next = TaskName::TASK_ERROR;
        }

        return $this->next == TaskName::TASK_ERROR ? ExitCode::FAIL : ExitCode::SUCCESS;
    }

    /**
     * @throws DistributionApiException
     * @throws UpgradeException
     * @throws Exception
     */
    public function downloadArchive(): void
    {
        $destPath = realpath($this->container->getProperty(UpgradeContainer::TMP_RELEASES_DIR)) . DIRECTORY_SEPARATOR . $this->container->getProperty(UpgradeContainer::ARCHIVE_FILENAME);
        $archiveUrl = $this->container->getUpgrader()->getOnlineDestinationRelease()->getZipDownloadUrl();

        try {
            $this->container->getDownloadService()->downloadWithRetry($archiveUrl, $destPath);

            $md5file = md5_file(realpath($this->container->getProperty(UpgradeContainer::ARCHIVE_FILEPATH)));
            if ($md5file == $this->container->getUpgrader()->getOnlineDestinationRelease()->getZipMd5()) {
                $this->next = TaskName::TASK_UNZIP;
                $this->logger->info($this->translator->trans('Download complete. Now extracting...'));
            } else {
                $this->logger->error($this->translator->trans('Download complete but MD5 sum does not match (%s).', [$md5file]));
                $this->next = TaskName::TASK_ERROR;
            }
        } catch (UpgradeException $e) {
            $this->logger->error($this->translator->trans('The .zip archive could not be downloaded. The update is currently impossible. Please try again later.'));
            $this->next = TaskName::TASK_ERROR;
        }
    }
}
