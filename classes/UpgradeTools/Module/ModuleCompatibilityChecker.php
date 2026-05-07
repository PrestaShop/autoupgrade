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

use PrestaShop\Module\AutoUpgrade\Exceptions\MarketplaceApiException;
use PrestaShop\Module\AutoUpgrade\Models\Module\DistributionApi\Module;
use PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace\ModuleUpgradeCompatibility;
use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use PrestaShop\Module\AutoUpgrade\Services\MarketplaceService;

class ModuleCompatibilityChecker
{
    // Check all modules and return details from the marketplace
    const DETAILED_SEARCH = 'detailed';
    // Check all modules
    const COMPLETE_SEARCH = 'complete';
    // Return on the first module with issue
    const QUICK_SEARCH = 'quick';

    /** @var DistributionApiService */
    private $distributionApiService;
    /** @var MarketplaceService */
    private $marketplaceService;

    public function __construct(
        DistributionApiService $distributionApiService,
        MarketplaceService $marketplaceService
    ) {
        $this->distributionApiService = $distributionApiService;
        $this->marketplaceService = $marketplaceService;
    }

    /**
     * @param array<array{name: string, currentVersion: string}> $modulesInstalled
     * @param self::*_SEARCH $mode
     *
     * @return array{incompatible_modules: string[], uncertain_modules: string[], compatibility: array<string, ?ModuleUpgradeCompatibility>}
     */
    public function getModulesRequiringAttention(array $modulesInstalled, string $targetVersion, ?string $sourceVersion = null, $mode = self::COMPLETE_SEARCH): array
    {
        $result = [
            'incompatible_modules' => [],
            'uncertain_modules' => [],
            'compatibility' => [],
        ];

        $modulesNamesFromDistributionApi = array_map(
            function (Module $module) { return $module->getName(); },
            $this->distributionApiService->getModules($targetVersion)
        );

        foreach ($modulesInstalled as $localModule) {
            $localModuleName = $localModule['name'];

            // Do not check on Marketplace API if known on Distribution API
            $moduleIsNative = in_array($localModuleName, $modulesNamesFromDistributionApi);
            if ($mode !== self::DETAILED_SEARCH && $moduleIsNative) {
                continue;
            }

            $localVersion = $localModule['currentVersion'];
            $result['compatibility'][$localModuleName] = null;

            try {
                $moduleDetails = $this->marketplaceService->getModuleDetail($localModuleName);
            } catch (MarketplaceApiException $e) {
                if (!$moduleIsNative) {
                    $result['uncertain_modules'][] = $localModuleName;
                }

                if ($mode === self::QUICK_SEARCH) {
                    return $result;
                }
                continue;
            }

            $moduleCompatibility = $this->marketplaceService->findCompatibleModuleUpgrade(
                $moduleDetails,
                $targetVersion,
                $localVersion
            );

            $result['compatibility'][$localModuleName] = $moduleCompatibility;

            if (!$moduleCompatibility->getLatestRelease()) {
                if (!$moduleIsNative) {
                    $result['uncertain_modules'][] = $localModuleName;
                }

                if ($mode === self::QUICK_SEARCH) {
                    return $result;
                }
            } elseif (!$moduleCompatibility->isCompatible()) {
                if (!$moduleIsNative) {
                    if ($sourceVersion && !$this->marketplaceService->findCompatibleModuleUpgrade($moduleDetails, $sourceVersion, $localVersion)->isCompatible()) {
                        $result['uncertain_modules'][] = $localModuleName;
                    } else {
                        $result['incompatible_modules'][] = $localModuleName;
                    }
                }

                if ($mode === self::QUICK_SEARCH) {
                    return $result;
                }
            }
        }

        return $result;
    }
}
