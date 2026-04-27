<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

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
