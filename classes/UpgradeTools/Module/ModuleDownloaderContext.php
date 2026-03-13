<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

use LogicException;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;

class ModuleDownloaderContext
{
    /** @var string */
    private $moduleName;

    /** @var string */
    private $referenceVersion;

    /** @var ModuleSource[]|null */
    private $updateSources;

    /** @var string|null */
    private $pathToModuleUpdate;

    /**
     * @param array{name:string, currentVersion:string} $moduleInfos
     */
    public function __construct(array $moduleInfos)
    {
        $this->moduleName = $moduleInfos['name'];
        $this->referenceVersion = $moduleInfos['currentVersion'];

        $this->validate();
    }

    /**
     * @throws LogicException
     */
    public function validate(): void
    {
        if (empty($this->moduleName)) {
            throw new LogicException('Module name is invalid.');
        }

        // TODO: Check version format as well?
        if (empty($this->referenceVersion)) {
            throw new LogicException('Module version is invalid.');
        }
    }

    public function getPathToModuleUpdate(): ?string
    {
        return $this->pathToModuleUpdate;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getReferenceVersion(): string
    {
        return $this->referenceVersion;
    }

    /**
     * @return ModuleSource[]|null
     */
    public function getUpdateSources(): ?array
    {
        return $this->updateSources;
    }

    /**
     * @param ModuleSource[] $moduleSources
     */
    public function setUpdateSources(array $moduleSources): self
    {
        $this->updateSources = $moduleSources;

        return $this;
    }

    public function setPathToModuleUpdate(string $pathToModuleUpdate): self
    {
        $this->pathToModuleUpdate = $pathToModuleUpdate;

        return $this;
    }
}
