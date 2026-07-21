<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade;

use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Exceptions\ProcessException;
use PrestaShop\Module\AutoUpgrade\Models\PrestashopRelease;
use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;
use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use PrestaShop\Module\AutoUpgrade\Services\PhpVersionResolverService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use PrestaShop\Module\AutoUpgrade\Xml\FileLoader;
use Symfony\Component\Filesystem\Filesystem;

class Upgrader
{
    const DEFAULT_CHECK_VERSION_DELAY_HOURS = 12;

    /** @var Translator */
    protected $translator;
    /** @var array<string, PrestashopRelease> */
    private $onlineDestinationReleases;
    /** @var string */
    protected $currentPsVersion;
    /** @var PhpVersionResolverService */
    protected $phpVersionResolverService;
    /** @var UpdateConfiguration */
    protected $updateConfiguration;
    /** @var Filesystem */
    protected $filesystem;
    /** @var FileLoader */
    protected $fileLoader;
    /** @var DistributionApiService */
    protected $distributionApiService;

    public function __construct(
        Translator $translator,
        PhpVersionResolverService $phpRequirementService,
        UpdateConfiguration $updateConfiguration,
        Filesystem $filesystem,
        FileLoader $fileLoader,
        DistributionApiService $distributionApiService,
        string $currentPsVersion
    ) {
        $this->translator = $translator;
        $this->phpVersionResolverService = $phpRequirementService;
        $this->updateConfiguration = $updateConfiguration;
        $this->filesystem = $filesystem;
        $this->fileLoader = $fileLoader;
        $this->distributionApiService = $distributionApiService;
        $this->currentPsVersion = $currentPsVersion;
    }

    /**
     * @throws DistributionApiException
     * @throws ProcessException
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
     */
    public function isNewerVersionAvailableOnline(): bool
    {
        $releaseOptions = $this->getOnlineDestinationReleases();
        if (empty($releaseOptions)) {
            return false;
        }

        return true;
    }

    /**
     * @throws DistributionApiException
     */
    public function getOnlineMaxDestinationRelease(): ?PrestashopRelease
    {
        return $this->getOnlineDestinationReleases()[PhpVersionResolverService::AVAILABLE_RELEASE_MAX] ?? null;
    }

    /**
     * @return array<string, PrestashopRelease>
     *
     * @throws DistributionApiException
     */
    public function getOnlineDestinationReleases(): array
    {
        if ($this->onlineDestinationReleases !== null) {
            return $this->onlineDestinationReleases;
        }
        $this->onlineDestinationReleases = $this->phpVersionResolverService->getPrestashopDestinationReleases(PHP_VERSION_ID);

        return $this->onlineDestinationReleases;
    }

    /**
     * @throws DistributionApiException
     * @throws ProcessException
     */
    public function getOnlineDestinationRelease(): ?PrestashopRelease
    {
        if ($this->updateConfiguration->isChannelOnline()) {
            return !empty($this->getOnlineDestinationReleases()[PhpVersionResolverService::AVAILABLE_RELEASE_MAX])
                ? $this->getOnlineDestinationReleases()[PhpVersionResolverService::AVAILABLE_RELEASE_MAX]
                : null;
        } elseif ($this->updateConfiguration->isChannelOnlineRecommended()) {
            return !empty($this->getOnlineDestinationReleases()[PhpVersionResolverService::AVAILABLE_RELEASE_RECOMMENDED])
                ? $this->getOnlineDestinationReleases()[PhpVersionResolverService::AVAILABLE_RELEASE_RECOMMENDED]
                : null;
        }

        return null;
    }

    public function getOnlineRecommendedDestinationRelease(): ?PrestashopRelease
    {
        $releases = $this->getOnlineDestinationReleases();

        if (empty($releases[PhpVersionResolverService::AVAILABLE_RELEASE_RECOMMENDED])) {
            return null;
        }

        $candidate = $releases[PhpVersionResolverService::AVAILABLE_RELEASE_RECOMMENDED];

        if (version_compare($this->currentPsVersion, $candidate->getVersion(), '>=')) {
            // Do not suggest if the available version is older
            return null;
        }

        return $candidate;
    }

    /**
     * @return ?string Prestashop destination version or null if no compatible version found
     *
     * @throws DistributionApiException
     * @throws ProcessException
     */
    public function getDestinationVersion(): ?string
    {
        if ($this->updateConfiguration->isChannelLocal()) {
            return $this->updateConfiguration->getLocalChannelVersion();
        } elseif ($this->updateConfiguration->isChannelOnline() || $this->updateConfiguration->isChannelOnlineRecommended()) {
            $release = $this->getOnlineDestinationRelease();

            if ($release) {
                return $release->getVersion();
            }
        }

        return null;
    }

    /**
     * @throws ProcessException
     */
    public function getOnlineDestinationVersionForChannel(string $channel): ?string
    {
        if ($channel === UpdateConfiguration::CHANNEL_ONLINE) {
            return $this->getOnlineMaxDestinationRelease() ? $this->getOnlineMaxDestinationRelease()->getVersion() : null;
        } elseif ($channel === UpdateConfiguration::CHANNEL_ONLINE_RECOMMENDED) {
            return $this->getOnlineRecommendedDestinationRelease() ? $this->getOnlineRecommendedDestinationRelease()->getVersion() : null;
        }

        throw new ProcessException(sprintf('Channel accepted: %s, %s', UpdateConfiguration::CHANNEL_ONLINE, UpdateConfiguration::CHANNEL_ONLINE_RECOMMENDED));
    }

    /**
     * @throws DistributionApiException
     * @throws ProcessException
     */
    public function getLatestCompatibleModuleVersion(): string
    {
        $autoupgradeReleases = $this->distributionApiService->getAutoupgradeCompatibilities();

        if (empty($autoupgradeReleases)) {
            throw new ProcessException($this->translator->trans('Unable to retrieve the recommended releases of Update Assistant.'));
        }

        $destinationVersion = $this->getDestinationVersion();

        $eligibleAutoupgradeReleases = array_filter($autoupgradeReleases, function ($autoupgradeRelease) use ($destinationVersion) {
            return $autoupgradeRelease->getPrestashopMinVersion() <= $destinationVersion && $autoupgradeRelease->getPrestashopMaxVersion() >= $destinationVersion;
        });

        $autoupgradeRelease = reset($eligibleAutoupgradeReleases);

        return $autoupgradeRelease ? $autoupgradeRelease->getRecommendedVersion() : '';
    }

    /**
     * delete the file /config/xml/$version.xml if exists.
     */
    public function clearXmlMd5File(string $version): void
    {
        $fileToRemove = _PS_ROOT_DIR_ . '/config/xml/' . $version . '.xml';
        if ($this->filesystem->exists($fileToRemove)) {
            $this->filesystem->remove($fileToRemove);
        }
    }
}
