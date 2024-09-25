<?php

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleDownloaderContext;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider\AbstractModuleSourceProvider;

class ModuleSourceAggregate
{
    /** @var AbstractModuleSourceProvider[] */
    private $providers;

    /**
     * @param AbstractModuleSourceProvider[] $sourceProviders Ordered by priority (first provider has top priority)
     */
    public function __construct(array $sourceProviders)
    {
        $this->providers = $sourceProviders;
    }

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

        $moduleContext->setUpdateSources(
            $this->orderSources($updateSources)
        );
    }

    /**
     * @param ModuleSource[] $sources
     *
     * @return ModuleSource[]
     */
    private function orderSources(array $sources): array
    {
        usort($sources, function (ModuleSource $source1, ModuleSource $source2) {
            return version_compare($source2->getNewVersion(), $source1->getNewVersion());
        });

        return $sources;
    }
}
