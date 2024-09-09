<?php

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;

abstract class AbstractModuleSourceProvider
{
    /** @var ModuleSource[]|null */
    protected $localModuleZips;

    /**
     * The provider is able to return a list of new versions for a given module in a current version.
     * If the module is unknown to the provider or if there is no new version available, the array is empty.
     *
     * @return ModuleSource[]
     */
    public function getUpdatesOfModule(string $moduleName, string $currentVersion): array
    {
        if (null === $this->localModuleZips) {
            $this->warmUp();
        }

        $sources = [];

        foreach ($this->localModuleZips as $zip) {
            if ($zip->getName() === $moduleName && version_compare($zip->getNewVersion(), $currentVersion, '>')) {
                $sources[] = $zip;
            }
        }

        return $sources;
    }

    abstract public function warmUp(): void;
}
