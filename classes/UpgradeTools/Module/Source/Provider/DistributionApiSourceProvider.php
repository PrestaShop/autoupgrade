<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
