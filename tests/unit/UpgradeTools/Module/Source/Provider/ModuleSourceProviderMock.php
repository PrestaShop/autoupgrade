<?php

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider\AbstractModuleSourceProvider;

class ModuleSourceProviderMock extends AbstractModuleSourceProvider
{
    private $sources;

    /** {@inheritdoc} */
    public function getUpdatesOfModule(string $moduleName, string $currentVersion): array
    {
        return $this->sources;
    }

    public function warmUp(): void
    {
        // Do nothing
    }

    /** @return ModuleSources[] */
    public function setSources($sources): self
    {
        $this->sources = $sources;

        return $this;
    }
}
