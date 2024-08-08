<?php

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;

interface ModuleSourceProviderInterface
{
    /**
     * The provider is able to return a list of new versions for a given module in a current version.
     * If the module is unknown to the provider or if there is no new version available, the array is empty.
     *
     * @return ModuleSource[]
     */
    public function getUpdatesOfModule(string $moduleName, string $currentVersion): array;
}
