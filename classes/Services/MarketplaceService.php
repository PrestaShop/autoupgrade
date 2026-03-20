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

use PrestaShop\Module\AutoUpgrade\Exceptions\MarketplaceApiException;
use PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace\Module;
use PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace\ModuleUpgradeCompatibility;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class MarketplaceService
{
    /** @var Translator */
    private $translator;

    /** @var array<string, Module|MarketplaceApiException> */
    private $cache = [];

    const ADDONS_API_URL = 'https://api.addons.prestashop.com';

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Fetches raw response bodies for multiple modules.
     * Returns a map of module name => response body string, or false on failure.
     *
     * @param string[] $toFetch
     *
     * @return array<string, string|false>
     */
    protected function fetchModulesRaw(array $toFetch): array
    {
        $multiHandle = curl_multi_init();
        $handles = [];

        foreach ($toFetch as $name) {
            $ch = curl_init(self::ADDONS_API_URL . '/v2/products/' . $name);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_multi_add_handle($multiHandle, $ch);
            $handles[$name] = $ch;
        }

        $running = 0;
        do {
            curl_multi_exec($multiHandle, $running);
            if ($running > 0 && curl_multi_select($multiHandle, 1.0) === -1) {
                usleep(100);
            }
        } while ($running > 0);

        $raw = [];
        foreach ($handles as $name => $ch) {
            $body = curl_multi_getcontent($ch);
            $errno = curl_errno($ch);
            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
            $raw[$name] = ($errno || $body === null) ? false : $body;
        }

        curl_multi_close($multiHandle);

        return $raw;
    }

    /**
     * Fetches details for multiple modules in parallel via curl_multi.
     * Returns a map of module name => Module on success, or MarketplaceApiException on failure.
     *
     * @param string[] $moduleNames
     *
     * @return array<string, Module|MarketplaceApiException>
     */
    public function getModuleDetails(array $moduleNames): array
    {
        $toFetch = [];
        $result = [];

        foreach ($moduleNames as $name) {
            if (isset($this->cache[$name])) {
                $result[$name] = $this->cache[$name];
            } else {
                $toFetch[] = $name;
            }
        }

        if (empty($toFetch)) {
            return $result;
        }

        $raw = $this->fetchModulesRaw($toFetch);

        // Ensure every requested name gets a result, even if fetchModulesRaw omits it.
        foreach ($toFetch as $name) {
            if (!array_key_exists($name, $raw)) {
                $raw[$name] = false;
            }
        }

        foreach ($raw as $name => $body) {
            if (!$body) {
                $value = new MarketplaceApiException(
                    $this->translator->trans('Error when retrieving data from Marketplace API'),
                    MarketplaceApiException::API_NOT_CALLABLE_CODE
                );
            } else {
                $data = json_decode($body, true);
                if (!$data || !is_array($data)) {
                    $value = new MarketplaceApiException(
                        $this->translator->trans('Unable to retrieve module %s information. Ignored.', [$name]),
                        MarketplaceApiException::EMPTY_DATA_CODE
                    );
                } else {
                    $value = Module::fromArray($data);
                }
            }

            $this->cache[$name] = $value;
            $result[$name] = $value;
        }

        return $result;
    }

    /**
     * @throws MarketplaceApiException
     */
    public function getModuleDetail(string $module): Module
    {
        $results = $this->getModuleDetails([$module]);
        $value = $results[$module];
        if ($value instanceof MarketplaceApiException) {
            throw $value;
        }

        return $value;
    }

    /**
     * Allows you to get compatibility information for a module based on the target version of PrestaShop.
     */
    public function findCompatibleModuleUpgrade(
        Module $module,
        string $psTargetVersion,
        string $localModuleVersion
    ): ModuleUpgradeCompatibility {
        $releases = $module->technicalInfo->releases;

        $compatibleReleases = [];
        $latestRelease = null;

        foreach ($releases as $release) {
            if (!$latestRelease || version_compare($release->productVersion, $latestRelease->productVersion, '>')) {
                $latestRelease = $release;
            }

            if (version_compare($psTargetVersion, $release->compatibleFrom, '>=') &&
                version_compare($psTargetVersion, $release->compatibleTo, '<=')) {
                $compatibleReleases[] = $release;
            }
        }

        if (empty($compatibleReleases)) {
            return new ModuleUpgradeCompatibility(
                false,
                false,
                $latestRelease
            );
        }

        usort($compatibleReleases, function ($a, $b) {
            return version_compare($b->productVersion, $a->productVersion);
        });
        $bestCompatible = $compatibleReleases[0];

        return new ModuleUpgradeCompatibility(
            true,
            version_compare($bestCompatible->productVersion, $localModuleVersion, '>'),
            $latestRelease,
            $bestCompatible
        );
    }
}
