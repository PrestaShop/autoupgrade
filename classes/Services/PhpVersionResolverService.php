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

namespace PrestaShop\Module\AutoUpgrade\Services;

use LogicException;
use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Models\PrestashopRelease;
use PrestaShop\Module\AutoUpgrade\VersionUtils;
use PrestaShop\Module\AutoUpgrade\Xml\FileLoader;

class PhpVersionResolverService
{
    const COMPATIBILITY_INVALID = 0;
    const COMPATIBILITY_VALID = 1;
    const COMPATIBILITY_UNKNOWN = 2;

    /** @var DistributionApiService */
    private $distributionApiService;
    /** @var FileLoader */
    private $fileLoader;
    /** @var string */
    private $currentPsVersion;

    /**
     * @param DistributionApiService $distributionApiService
     */
    public function __construct(DistributionApiService $distributionApiService, FileLoader $fileLoader, string $currentPsVersion)
    {
        $this->distributionApiService = $distributionApiService;
        $this->fileLoader = $fileLoader;
        $this->currentPsVersion = $currentPsVersion;
    }

    /**
     * @return array{"php_min_version": string, "php_max_version": string, "php_current_version": string}|null
     */
    public function getPhpCompatibilityRange(string $targetVersion): ?array
    {
        if (version_compare($targetVersion, '8', '<')) {
            $targetMinorVersion = VersionUtils::splitPrestaShopVersion($targetVersion)['minor'];

            $requirements = $this->getPrestashop17Requirements();
            if (isset($requirements[$targetMinorVersion])) {
                $range = [
                    'php_min_version' => $requirements[$targetMinorVersion]->getPhpMinVersion(),
                    'php_max_version' => $requirements[$targetMinorVersion]->getPhpMaxVersion(),
                ];
            }

            if (!isset($range)) {
                return null;
            }
        } else {
            try {
                $range = $this->distributionApiService->getPhpVersionRequirements($targetVersion);
            } catch (DistributionApiException $apiException) {
                return null;
            }
        }
        $currentPhpVersion = VersionUtils::getHumanReadableVersionOf(PHP_VERSION_ID);
        $range['php_current_version'] = $currentPhpVersion;

        return $range;
    }

    /**
     * @return self::COMPATIBILITY_*
     */
    public function getPhpRequirementsState(int $currentPhpVersionId, ?string $currentPrestashopVersion): int
    {
        if (null == $currentPrestashopVersion) {
            return self::COMPATIBILITY_UNKNOWN;
        }

        $phpCompatibilityRange = $this->getPhpCompatibilityRange($currentPrestashopVersion);

        if (null == $phpCompatibilityRange) {
            return self::COMPATIBILITY_UNKNOWN;
        }

        $versionMin = VersionUtils::getPhpVersionId($phpCompatibilityRange['php_min_version']);
        $versionMax = VersionUtils::getPhpVersionId($phpCompatibilityRange['php_max_version']);

        $versionMinWithoutPatch = VersionUtils::getPhpMajorMinorVersionId($versionMin);
        $versionMaxWithoutPatch = VersionUtils::getPhpMajorMinorVersionId($versionMax);

        $currentVersion = VersionUtils::getPhpMajorMinorVersionId($currentPhpVersionId);

        if ($currentVersion >= $versionMinWithoutPatch && $currentVersion <= $versionMaxWithoutPatch) {
            return self::COMPATIBILITY_VALID;
        }

        return self::COMPATIBILITY_INVALID;
    }

    /**
     * @throws DistributionApiException
     * @throws UpgradeException
     */
    public function getPrestashopDestinationRelease(int $currentPhpVersionId): ?PrestashopRelease
    {
        $currentPhpVersion = VersionUtils::getPhpMajorMinorVersionId($currentPhpVersionId);

        if ($currentPhpVersion < 70100) {
            throw new LogicException('The minimum version to use the module is PHP 7.1');
        }

        /** @var array<string, PrestashopRelease> $releasesFromChannelFile */
        $releasesFromChannelFile = $this->getReleasesFromChannelFile();
        if (empty($releasesFromChannelFile)) {
            throw new UpgradeException('Unable to retrieve latest 1.7 release of Prestashop.');
        }

        if ($currentPhpVersion < 70200) {
            $release = $releasesFromChannelFile['1.7'];

            // current version is superior or equal
            if (version_compare($this->currentPsVersion, $release->getVersion(), '>=')) {
                return null;
            }

            return $release;
        }

        $validReleases = [];

        foreach ($this->distributionApiService->getReleases() as $release) {
            if ($release->getStability() !== 'stable') {
                continue;
            }

            // current version is superior or equal
            if (version_compare($this->currentPsVersion, $release->getVersion(), '>=')) {
                continue;
            }

            $versionMin = VersionUtils::getPhpVersionId($release->getPhpMinVersion());
            $versionMax = VersionUtils::getPhpVersionId($release->getPhpMaxVersion());

            $versionMinWithoutPatch = VersionUtils::getPhpMajorMinorVersionId($versionMin);
            $versionMaxWithoutPatch = VersionUtils::getPhpMajorMinorVersionId($versionMax);

            // verify php compatibility
            if ($currentPhpVersion >= $versionMinWithoutPatch && $currentPhpVersion <= $versionMaxWithoutPatch) {
                // verify channel.xml matching
                $branch = VersionUtils::splitPrestaShopVersion($release->getVersion())['minor'];
                if (isset($releasesFromChannelFile[$branch])) {
                    $releaseNote = $releasesFromChannelFile[$branch]->getReleaseNoteUrl();
                    $release->setReleaseNoteUrl($releaseNote);
                    $validReleases[] = $release;
                }
            }
        }

        /** @var PrestashopRelease $release */
        $release = array_reduce($validReleases, function ($carry, $item) {
            if ($carry === null || version_compare($item->getVersion(), $carry->getVersion()) > 0) {
                return $item;
            }

            return $carry;
        });

        return $release;
    }

    /**
     * @return array<string, PrestashopRelease>
     */
    private function getPrestashop17Requirements(): array
    {
        return [
            '1.7.0' => new PrestashopRelease('1.7.0.0', 'stable', '7.1', '5.4'),
            '1.7.1' => new PrestashopRelease('1.7.1.0', 'stable', '7.1', '5.4'),
            '1.7.2' => new PrestashopRelease('1.7.2.0', 'stable', '7.1', '5.4'),
            '1.7.3' => new PrestashopRelease('1.7.3.0', 'stable', '7.1', '5.4'),
            '1.7.4' => new PrestashopRelease('1.7.4.0', 'stable', '7.1', '5.6'),
            '1.7.5' => new PrestashopRelease('1.7.5.0', 'stable', '7.2', '5.6'),
            '1.7.6' => new PrestashopRelease('1.7.6.0', 'stable', '7.2', '5.6'),
            '1.7.7' => new PrestashopRelease('1.7.7.0', 'stable', '7.3', '5.6'),
            '1.7.8' => new PrestashopRelease('1.7.8.0', 'stable', '7.4', '5.6'),
        ];
    }

    /**
     * @return array<string, PrestashopRelease>
     *
     * @throws UpgradeException
     */
    public function getReleasesFromChannelFile(): array
    {
        $releases = [];
        $channelFile = $this->fileLoader->getXmlChannel();

        if (!$channelFile) {
            throw new UpgradeException('Unable to retrieve channel.xml from API.');
        }

        foreach ($channelFile->channel as $channel) {
            if ((string) $channel['name'] !== 'stable') {
                continue;
            }

            foreach ($channel->branch as $branch) {
                $cleanedZipUrl = str_replace(["\n", "\r"], '', $branch->download->link);
                $cleanedZipUrl = trim($cleanedZipUrl);

                $cleanedChangelogUrl = str_replace(["\n", "\r"], '', $branch->changelog);
                $cleanedChangelogUrl = trim($cleanedChangelogUrl);

                $releases[(string) $branch['name']] = new PrestashopRelease(
                        (string) $branch->num,
                    'stable',
                    null,
                    null,
                        $cleanedZipUrl,
                        'https://api.prestashop.com/xml/md5/' . $branch->num,
                        (string) $branch->download->md5,
                        $cleanedChangelogUrl
                    );
            }
        }

        return $releases;
    }
}
