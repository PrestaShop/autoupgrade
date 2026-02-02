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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider;

use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;

/*
 * Get the updates from the Distribution API.
 */
class DistributionApiSourceProvider extends AbstractModuleSourceProvider
{
    /** @var DistributionApiService */
    private $distributionApiService;

    /** @var FileStorage */
    private $fileConfigurationStorage;

    /** @var string */
    private $targetVersionOfPrestaShop;

    public function __construct(string $targetVersionOfPrestaShop, DistributionApiService $distributionApiService, FileStorage $fileConfigurationStorage)
    {
        $this->targetVersionOfPrestaShop = $targetVersionOfPrestaShop;
        $this->distributionApiService = $distributionApiService;
        $this->fileConfigurationStorage = $fileConfigurationStorage;
    }

    public function warmUp(): void
    {
        if ($this->fileConfigurationStorage->exists(UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_DISTRIBUTION_API)) {
            $this->localModuleZips = $this->fileConfigurationStorage->load(UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_DISTRIBUTION_API);

            return;
        }

        $modules = $this->distributionApiService->getModules($this->targetVersionOfPrestaShop);

        $this->localModuleZips = [];

        foreach ($modules as $moduleData) {
            $this->localModuleZips[] = new ModuleSource(
                $moduleData->getName(),
                $moduleData->getVersion(),
                $moduleData->getDownloadUrl(),
                true
            );
        }

        $this->fileConfigurationStorage->save($this->localModuleZips, UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_DISTRIBUTION_API);
    }
}
