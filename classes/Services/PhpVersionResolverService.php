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

use LogicException;
use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Models\PrestashopRelease;
use PrestaShop\Module\AutoUpgrade\VersionUtils;

class PhpVersionResolverService
{
    const COMPATIBILITY_INVALID = 0;
    const COMPATIBILITY_VALID = 1;
    const COMPATIBILITY_UNKNOWN = 2;

    const AVAILABLE_RELEASE_MAX = 'max';
    const AVAILABLE_RELEASE_RECOMMENDED = 'recommended';

    /** @var DistributionApiService */
    private $distributionApiService;
    /** @var string */
    private $currentPsVersion;

    public function __construct(DistributionApiService $distributionApiService, string $currentPsVersion)
    {
        $this->distributionApiService = $distributionApiService;
        $this->currentPsVersion = $currentPsVersion;
    }

    /**
     * @return array{"php_min_version": string, "php_max_version": string, "php_current_version": string}|null
     */
    public function getPhpCompatibilityRange(string $targetVersion): ?array
    {
        try {
            $range = $this->distributionApiService->getPhpVersionRequirements($targetVersion);
        } catch (DistributionApiException $apiException) {
            return null;
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
     * @return array<string, PrestaShopRelease>
     *
     * @throws DistributionApiException
     * @throws LogicException
     */
    public function getPrestashopDestinationReleases(int $currentPhpVersionId): array
    {
        $currentPhpVersion = VersionUtils::getPhpMajorMinorVersionId($currentPhpVersionId);

        if ($currentPhpVersion < 70100) {
            throw new LogicException('The minimum version to use the module is PHP 7.1');
        }

        $releases = $this->distributionApiService->getReleases();
        $autoupgradeCompatibilities = $this->distributionApiService->getAutoupgradeCompatibilities();

        $validReleases = [];

        foreach ($releases as $release) {
            if ($release->getStability() !== 'stable' || $release->getZipDownloadUrl() === null || $release->getXmlDownloadUrl() === null) {
                continue;
            }

            // current version is superior or equal
            if (version_compare($this->currentPsVersion, $release->getVersion(), '>=')) {
                continue;
            }

            // add check compat with autoupgrade module

            $isEligibleForUpdate = false;

            foreach ($autoupgradeCompatibilities as $autoupgradeCompatibility) {
                if (version_compare($autoupgradeCompatibility->getPrestashopMaxVersion(), $release->getVersion(), '>=') && version_compare($autoupgradeCompatibility->getPrestashopMinVersion(), $release->getVersion(), '<=')) {
                    $isEligibleForUpdate = true;
                }
            }

            if (!$isEligibleForUpdate) {
                continue;
            }

            // add check compat with autoupgrade endpoint

            $versionMin = VersionUtils::getPhpVersionId($release->getPhpMinVersion());
            $versionMax = VersionUtils::getPhpVersionId($release->getPhpMaxVersion());

            $versionMinWithoutPatch = VersionUtils::getPhpMajorMinorVersionId($versionMin);
            $versionMaxWithoutPatch = VersionUtils::getPhpMajorMinorVersionId($versionMax);

            // verify php compatibility
            if ($currentPhpVersion >= $versionMinWithoutPatch && $currentPhpVersion <= $versionMaxWithoutPatch) {
                $validReleases[] = $release;
            }
        }

        $releaseResult = [];

        $maxRelease = null;
        $recommendedRelease = null;
        $fallbackRecommendedRelease = null;

        foreach ($validReleases as $releaseItem) {
            // Determine max release (latest version)
            if ($maxRelease === null || version_compare($releaseItem->getVersion(), $maxRelease->getVersion(), '>')) {
                $maxRelease = $releaseItem;
            }

            // Determine recommended release based on autoupgrade compatibilities
            foreach ($autoupgradeCompatibilities as $compatibility) {
                $isRecommendedRelease = $compatibility->isRecommended()
                    && version_compare($compatibility->getPrestashopMaxVersion(), $releaseItem->getVersion(), '>=')
                    && version_compare($compatibility->getPrestashopMinVersion(), $releaseItem->getVersion(), '<=')
                    && ($recommendedRelease === null || version_compare($releaseItem->getVersion(), $recommendedRelease->getVersion(), '>'));

                if ($isRecommendedRelease) {
                    $recommendedRelease = $releaseItem;
                }
            }

            $updateType = VersionUtils::getUpdateType($this->currentPsVersion, $releaseItem->getVersion());
            $isMinorOrPatchUpdate = $updateType === 'minor' || $updateType === 'patch';

            if ($isMinorOrPatchUpdate && ($fallbackRecommendedRelease === null || version_compare($releaseItem->getVersion(), $fallbackRecommendedRelease->getVersion(), '>'))) {
                $fallbackRecommendedRelease = $releaseItem;
            }
        }

        // Build result array only if releases are found
        if ($maxRelease !== null) {
            $releaseResult[self::AVAILABLE_RELEASE_MAX] = $maxRelease;
        }

        if ($recommendedRelease !== null) {
            $releaseResult[self::AVAILABLE_RELEASE_RECOMMENDED] = $recommendedRelease;
        } elseif ($fallbackRecommendedRelease !== null) {
            $releaseResult[self::AVAILABLE_RELEASE_RECOMMENDED] = $fallbackRecommendedRelease;
        }

        return $releaseResult;
    }
}
