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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

use Exception;
use LogicException;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Services\DownloadService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Component\Filesystem\Filesystem;

class ModuleDownloader
{
    /** @var Translator */
    private $translator;

    /** @var DownloadService */
    private $downloadService;

    /** @var Logger */
    private $logger;

    /** @var string */
    private $downloadFolder;

    public function __construct(DownloadService $downloadService, Translator $translator, Logger $logger, string $downloadFolder)
    {
        $this->downloadService = $downloadService;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->downloadFolder = $downloadFolder;
    }

    /**
     * @throws UpgradeException
     */
    public function downloadModule(ModuleDownloaderContext $moduleDownloaderContext): void
    {
        if (empty($moduleDownloaderContext->getUpdateSources())) {
            throw new LogicException('List of updates is empty.');
        }

        $downloadSuccessful = false;

        for ($i = 0; !$downloadSuccessful && $i < count($moduleDownloaderContext->getUpdateSources()); ++$i) {
            try {
                $this->attemptDownload($moduleDownloaderContext, $i);
                $downloadSuccessful = true;
            } catch (Exception $e) {
                $this->logger->debug($e->getMessage());
                $this->logger->debug($this->translator->trans('Download of source #%s has failed.', [$i]));
            }
        }

        if (!$downloadSuccessful) {
            $message = $this->translator->trans('All download attempts have failed. The module %s has been disabled. You can try to update it manually afterwards.', [$moduleDownloaderContext->getModuleName()]);
            throw (new UpgradeException($message))->setSeverity(UpgradeException::SEVERITY_WARNING);
        }
    }

    /**
     * @throws UpgradeException
     */
    private function attemptDownload(ModuleDownloaderContext $moduleDownloaderContext, int $index): void
    {
        $moduleSource = $moduleDownloaderContext->getUpdateSources()[$index];
        $filesystem = new Filesystem();

        $destinationPath = $this->downloadFolder;

        if ($moduleSource->isZipped()) {
            $destinationPath .= '/' . $moduleDownloaderContext->getModuleName() . '.zip';
            $this->downloadService->downloadWithRetry($moduleSource->getPath(), $destinationPath);
        } else {
            // Module contents is already unzipped.
            // We move it first in the sandbox folder to make sure all the files can be read.
            $filesystem->mirror($moduleSource->getPath(), $this->downloadFolder);
        }

        $this->assertDownloadedContentsIsValid($destinationPath);
        $moduleDownloaderContext->setPathToModuleUpdate($destinationPath);

        $this->logger->notice($this->translator->trans('Module %s update files (%s => %s) have been fetched from %s.', [
            $moduleDownloaderContext->getModuleName(),
            $moduleDownloaderContext->getReferenceVersion(),
            $moduleSource->getNewVersion(),
            $moduleSource->getPath(),
        ]));
    }

    /**
     * @throws UpgradeException If download content is invalid
     */
    private function assertDownloadedContentsIsValid(string $destinationPath): void
    {
        if (is_file($destinationPath)) {
            // Arbitrary size value checking the zip file has at least a file in it.
            if (filesize($destinationPath) <= 300) {
                throw (new UpgradeException($this->translator->trans('The received module archive is empty.')))->setSeverity(UpgradeException::SEVERITY_WARNING);
            }

            $downloadedFile = fopen($destinationPath, 'r');
            if (!$downloadedFile || fread($downloadedFile, 5) == '<?xml') {
                throw (new UpgradeException($this->translator->trans('Invalid contents from provider (Got an XML file).')))->setSeverity(UpgradeException::SEVERITY_WARNING);
            }
            fclose($downloadedFile);
        }
    }
}
