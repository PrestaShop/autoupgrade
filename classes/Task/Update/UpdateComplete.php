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
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Ends the upgrade process and displays the success message.
 */
class UpdateComplete extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_UPDATE;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        $state = $this->container->getUpdateState();
        $state->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $destinationVersion = $state->getDestinationVersion();

        $this->logger->info($state->isWarningDetected() ?
            $this->translator->trans('Shop updated to %s, but some warnings have been found.', [$destinationVersion]) :
            $this->translator->trans('Shop updated to %s. Congratulations! You can now reactivate your shop.', [$destinationVersion])
        );

        $this->next = TaskName::TASK_COMPLETE;

        $filesystem = $this->container->getFileSystem();
        $filePath = $this->container->getArchiveFilePath();
        $latestPath = $this->container->getProperty(UpgradeContainer::TMP_FILES_PATH);

        if ($filesystem->exists($filePath)) {
            if ($this->container->getUpdateConfiguration()->isChannelOnline()) {
                $this->removeFile($filePath);
            } else {
                $this->logger->debug($this->translator->trans('Please remove %s by FTP', [$filePath]));
            }
        }

        if ($filesystem->exists($latestPath)) {
            $this->removeFile($latestPath);
        }

        // removing config files
        $this->container->getFileStorage()->clean(UpgradeFileNames::UPDATE_CONFIG_FILENAME);

        // removing temporary files
        $tmpModulePath = $this->container->getProperty(UpgradeContainer::TMP_MODULES_PATH);
        if ($this->container->getFilesystemAdapter()->clearDirectory($tmpModulePath)) {
            $this->logger->debug($this->translator->trans('The temporary directory of modules has been emptied'));
        }

        $this->container->getFileStorage()->cleanAllUpdateFiles();
        $this->container->getAnalytics()->track('Upgrade Succeeded', Analytics::WITH_UPDATE_PROPERTIES);

        $this->logger->info($this->translator->trans('Running opcache_reset'));
        $this->container->resetOpcache();

        $this->container->getCacheCleaner()->cleanFolders();

        return ExitCode::SUCCESS;
    }

    private function removeFile(string $filePath): void
    {
        try {
            $this->container->getFileSystem()->remove($filePath);
            $this->logger->debug($this->translator->trans('%s removed', [$filePath]));
        } catch (IOException $e) {
            $this->logger->debug($this->translator->trans('Please remove %s by FTP', [$filePath]));
        }
    }
}
