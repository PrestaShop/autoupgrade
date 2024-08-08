<?php

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider\ModuleSourceProviderInterface;

class ModuleSourceProviderMock implements ModuleSourceProviderInterface
{
    private $sources;

    /** {@inheritdoc} */
    public function getUpdatesOfModule(string $moduleName, string $currentVersion): array
    {
        return $this->sources;
    }

    /** @return ModuleSources[] */
    public function setSources($sources): self
    {
        $this->sources = $sources;

        return $this;
    }
}
