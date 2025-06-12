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

use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Models\AutoupgradeRelease;
use PrestaShop\Module\AutoUpgrade\Models\PrestashopRelease;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class DistributionApiService
{
    public const PRESTASHOP_ENDPOINT = 'prestashop';
    public const AUTOUPGRADE_ENDPOINT = 'autoupgrade';
    public const API_URL = 'https://api.prestashop-project.org';

    /** @var array<string, string> */
    private static $factories = [
        self::PRESTASHOP_ENDPOINT => 'createPrestashopReleaseCollection',
        self::AUTOUPGRADE_ENDPOINT => 'createAutoupgradeReleaseCollection',
    ];

    /** @var Translator */
    private $translator;

    /**
     * @var array{ 'prestashop'?: PrestashopRelease[], 'autoupgrade'?: AutoupgradeRelease[] }
     */
    private $endpointData;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
        $this->endpointData = [];
    }

    /**
     * @throws DistributionApiException
     *
     * @param string $endPoint
     *
     * @return mixed|null
     */
    public function getApiEndpoint(string $endPoint)
    {
        $response = @file_get_contents(self::API_URL . '/' . $endPoint);

        if (!$response) {
            throw new DistributionApiException($this->translator->trans('Error when retrieving data from Distribution API'), DistributionApiException::API_NOT_CALLABLE_CODE);
        }

        $jsonResponse = json_decode($response, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new DistributionApiException($this->translator->trans('Invalid JSON from Distribution API: %s', [json_last_error_msg()]), DistributionApiException::API_NOT_CALLABLE_CODE);
        }

        return $jsonResponse;
    }

    /**
     * @throws DistributionApiException
     *
     * @return array{php_min_version: string, php_max_version: string}
     */
    public function getPhpVersionRequirements(string $targetVersion): array
    {
        $data = $this->getEndpointData(self::PRESTASHOP_ENDPOINT);

        foreach ($data as $prestashopRelease) {
            if ($prestashopRelease->getVersion() === $targetVersion) {
                return [
                    'php_min_version' => $prestashopRelease->getPhpMinVersion(),
                    'php_max_version' => $prestashopRelease->getPhpMaxVersion(),
                ];
            }
        }

        throw new DistributionApiException($this->translator->trans('No version match in Distribution api for %s', [$targetVersion]), DistributionApiException::VERSION_NOT_FOUND_CODE);
    }

    /**
     * @param string $version
     *
     * @return PrestashopRelease|null
     *
     * @throws DistributionApiException
     */
    public function getRelease(string $version): ?PrestashopRelease
    {
        $data = $this->getReleases();

        foreach ($data as $prestashopRelease) {
            if ($prestashopRelease->getVersion() === $version) {
                return $prestashopRelease;
            }
        }

        return null;
    }

    /**
     * @throws DistributionApiException
     *
     * @return PrestashopRelease[]
     */
    public function getReleases(): array
    {
        return $this->getEndpointData(self::PRESTASHOP_ENDPOINT);
    }

    /**
     * @return AutoupgradeRelease[]
     *
     * @throws DistributionApiException
     */
    public function getAutoupgradeCompatibilities(): array
    {
        return $this->getEndpointData(self::AUTOUPGRADE_ENDPOINT);
    }

    /**
     * @param self::PRESTASHOP_ENDPOINT|self::AUTOUPGRADE_ENDPOINT $endPoint
     *
     * @return AutoupgradeRelease[]|PrestashopRelease[]
     *
     * @throws DistributionApiException
     */
    private function getEndpointData(string $endPoint): array
    {
        if (!isset($this->endpointData[$endPoint])) {
            $jsonResponse = $this->getApiEndpoint($endPoint);

            if (empty($jsonResponse) || ($endPoint === self::AUTOUPGRADE_ENDPOINT && empty($jsonResponse['prestashop']))) {
                throw new DistributionApiException($this->translator->trans('Unable to retrieve "%s" data from distribution API.', [$endPoint]), DistributionApiException::EMPTY_DATA_CODE);
            }

            $method = self::$factories[$endPoint];

            $this->endpointData[$endPoint] = $this->$method($jsonResponse);
        }

        return $this->endpointData[$endPoint];
    }

    /**
     * @param mixed[] $data
     *
     * @return PrestashopRelease[]
     */
    private function createPrestashopReleaseCollection(array $data): array
    {
        $releases = [];
        foreach ($data as $versionInfo) {
            $releases[] = new PrestashopRelease(
                $versionInfo['version'],
                $versionInfo['stability'],
                $versionInfo['distribution'],
                $versionInfo['php_max_version'],
                $versionInfo['php_min_version'],
                $versionInfo['zip_download_url'],
                $versionInfo['xml_download_url'],
                $versionInfo['zip_md5'],
                $versionInfo['release_notes_url'],
                $versionInfo['distribution_version']
            );
        }

        return $releases;
    }

    /**
     * @param array{ 'prestashop': mixed[] } $data
     *
     * @return AutoupgradeRelease[]
     */
    private function createAutoupgradeReleaseCollection(array $data): array
    {
        $releases = [];

        foreach ($data['prestashop'] as $versionInfo) {
            $releases[] = new AutoupgradeRelease(
                $versionInfo['prestashop_min'],
                $versionInfo['prestashop_max'],
                $versionInfo['autoupgrade_recommended']['last_version'],
                $versionInfo['autoupgrade_recommended']['download']['link'],
                $versionInfo['autoupgrade_recommended']['download']['md5'],
                $versionInfo['autoupgrade_recommended']['changelog'] ?? null
            );
        }

        return $releases;
    }
}
