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

namespace PrestaShop\Module\AutoUpgrade\Services;

use PrestaShop\Module\AutoUpgrade\Exceptions\ProcessException;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class DownloadService
{
    const MAX_DOWNLOAD_TRY = 3;
    const WAIT_BETWEEN_RETRY_IN_SECONDS = 2;

    /** @var Logger */
    private $logger;
    /** @var Translator */
    private $translator;

    public function __construct(Translator $translator, Logger $logger)
    {
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * @throws ProcessException
     */
    public function downloadWithRetry(string $downloadUrl, string $destinationPath, int $retryCount = self::MAX_DOWNLOAD_TRY, int $delayInSeconds = self::WAIT_BETWEEN_RETRY_IN_SECONDS): void
    {
        $attempt = 0;

        while ($attempt < $retryCount) {
            ++$attempt;

            try {
                $this->download($downloadUrl, $destinationPath);

                return;
            } catch (IOException $exception) {
                $this->logger->debug($this->translator->trans('Download attempt %d/%d failed: %s', [$attempt, $retryCount, $exception->getMessage()]));
            }

            if ($attempt < $retryCount) {
                $this->wait($delayInSeconds);
            }
        }

        throw new ProcessException($this->translator->trans('All download attempts have failed.'));
    }

    public function download(string $downloadUrl, string $destinationPath): void
    {
        $filesystem = new Filesystem();
        $filesystem->copy($downloadUrl, $destinationPath);

        if (!is_file($destinationPath) || filesize($destinationPath) === 0) {
            throw new IOException($this->translator->trans('The file could not be downloaded or is empty. Destination path: "%s", Source URL: "%s".', [$destinationPath, $downloadUrl]));
        }
    }

    private function wait(int $seconds): void
    {
        sleep($seconds);
    }
}
