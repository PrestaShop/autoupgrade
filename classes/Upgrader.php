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

namespace PrestaShop\Module\AutoUpgrade;

use LogicException;
use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Models\PrestashopRelease;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Services\PhpVersionResolverService;
use PrestaShop\Module\AutoUpgrade\Xml\FileLoader;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class Upgrader
{
    const CHANNEL_DYNAMIC = 'dynamic';
    const CHANNEL_ARCHIVE = 'archive';

    const DEFAULT_CHECK_VERSION_DELAY_HOURS = 12;
    const DEFAULT_CHANNEL = self::CHANNEL_DYNAMIC;
    const DEFAULT_FILENAME = 'prestashop.zip';

    /** @var string */
    private $channel;

    /** @var PrestashopRelease */
    private $destinationRelease;
    /** @var string */
    protected $currentPsVersion;
    /** @var PhpVersionResolverService */
    protected $phpVersionResolverService;
    /** @var UpgradeConfiguration */
    protected $upgradeConfiguration;

    public function __construct(
        PhpVersionResolverService $phpRequirementService,
        UpgradeConfiguration $upgradeConfiguration,
        string $currentPsVersion
    ) {
        $this->currentPsVersion = $currentPsVersion;
        $this->phpVersionResolverService = $phpRequirementService;
        $this->channel = $upgradeConfiguration->getChannel();
        $this->upgradeConfiguration = $upgradeConfiguration;
    }

    /**
     * downloadLast download the last version of PrestaShop and save it in $dest/$filename.
     *
     * @param string $dest directory where to save the file
     * @param string $filename new filename
     *
     * @throws DistributionApiException
     * @throws UpgradeException
     *
     * @TODO ftp if copy is not possible (safe_mode for example)
     */
    public function downloadLast(string $dest, string $filename = 'prestashop.zip'): bool
    {
        if ($this->destinationRelease === null) {
            $this->getDynamicDestinationRelease();
        }

        $destPath = realpath($dest) . DIRECTORY_SEPARATOR . $filename;

        try {
            $filesystem = new Filesystem();
            $filesystem->copy($this->destinationRelease->getZipDownloadUrl(), $destPath);
        } catch (IOException $e) {
            // If the Symfony filesystem failed, we can try with
            // the legacy method which uses curl.
            Tools14::copy($this->destinationRelease->getZipDownloadUrl(), $destPath);
        }

        return is_file($destPath);
    }

    /**
     * @throws DistributionApiException
     * @throws UpgradeException
     */
    public function isLastVersion(): bool
    {
        if ($this->getDestinationVersion() === null) {
            return true;
        }

        return version_compare($this->currentPsVersion, $this->getDestinationVersion(), '>=');
    }

    /**
     * @throws DistributionApiException
     * @throws UpgradeException
     */
    public function getDynamicDestinationRelease(): ?PrestashopRelease
    {
        if ($this->channel !== self::CHANNEL_DYNAMIC) {
            throw new LogicException('channel must be dynamic to retrieve the version dynamically');
        }

        if ($this->destinationRelease !== null) {
            return $this->destinationRelease;
        }
        $this->destinationRelease = $this->phpVersionResolverService->getPrestashopDestinationRelease(PHP_VERSION_ID);

        return $this->destinationRelease;
    }

    /**
     * @return ?string Prestashop destination version or null if no compatible version found
     *
     * @throws DistributionApiException
     * @throws UpgradeException
     */
    public function getDestinationVersion(): ?string
    {
        if ($this->channel === self::CHANNEL_ARCHIVE) {
            return $this->upgradeConfiguration->get('archive.version_num');
        } else {
            return $this->getDynamicDestinationRelease() ? $this->getDynamicDestinationRelease()->getVersion() : null;
        }
    }

    /**
     * @throws UpgradeException
     */
    public function getLatestModuleVersion(): string
    {
        $fileLoader = new FileLoader();

        $channelFile = $fileLoader->getXmlChannel();

        if (empty($channelFile)) {
            throw new UpgradeException('Unable to retrieve channel.xml.');
        }

        return $channelFile->autoupgrade->last_version;
    }

    /**
     * delete the file /config/xml/$version.xml if exists.
     */
    public function clearXmlMd5File(string $version): bool
    {
        if (file_exists(_PS_ROOT_DIR_ . '/config/xml/' . $version . '.xml')) {
            return unlink(_PS_ROOT_DIR_ . '/config/xml/' . $version . '.xml');
        }

        return true;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }
}
