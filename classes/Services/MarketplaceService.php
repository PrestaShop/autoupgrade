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

    const ADDONS_API_URL = 'https://api.addons.prestashop.com';

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @throws MarketplaceApiException
     */
    public function getModuleDetail(string $module): Module
    {
        $response = file_get_contents(self::ADDONS_API_URL . '/v2/products/' . $module);

        if (!$response) {
            throw new MarketplaceApiException($this->translator->trans('Error when retrieving data from Marketplace API'), MarketplaceApiException::API_NOT_CALLABLE_CODE);
        }

        $data = json_decode($response, true);

        if (!$data || !is_array($data)) {
            throw new MarketplaceApiException($this->translator->trans('Unable to retrieve module %s information. Ignored.', [$module]), MarketplaceApiException::EMPTY_DATA_CODE);
        }

        return Module::fromArray($data);
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
