<?php

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleDownloaderContext;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider\ModuleSourceProviderInterface;

class ModuleSourceList
{
    /** @var ModuleSourceProviderInterface[] */
    private $providers;

    /**
     * @param ModuleSourceProviderInterface[] $sourceProviders
     */
    public function __construct(array $sourceProviders)
    {
        $this->providers = $sourceProviders;
    }

    /**
     * @return ModuleSource[]
     */
    public function setSourcesIn(ModuleDownloaderContext $moduleContext): void
    {
        $updateSources = [];
        foreach ($this->providers as $provider) {
            $updateSources = array_merge(
                $updateSources,
                $provider->getUpdatesOfModule(
                    $moduleContext->getModuleName(),
                    $moduleContext->getReferenceVersion()
                ));
        }
        $moduleContext->setUpdateSources($this->orderSources($updateSources));
    }

    /**
     * @param ModuleSource[]
     *
     * @return ModuleSource[]
     */
    private function orderSources(array $sources): array
    {
        usort($sources, function (ModuleSource $source1, ModuleSource $source2) {
            return version_compare($source2->getNewVersion(), $source1->getNewVersion());

            // TODO: Add provider priority check when versions are the same
        });

        return $sources;
    }
}
