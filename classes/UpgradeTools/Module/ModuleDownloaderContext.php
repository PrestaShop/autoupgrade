<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
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
