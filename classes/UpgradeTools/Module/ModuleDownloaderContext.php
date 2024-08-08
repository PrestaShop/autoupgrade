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

class ModuleDownloaderContext
{
    /** @var string */
    private $moduleName;

    /** @var string */
    private $referenceVersion;

    /** @var ModuleSource[]|null */
    private $updateSources;

    public function __construct(string $moduleName, string $referenceVersion)
    {
        $this->moduleName = $moduleName;
        $this->referenceVersion = $referenceVersion;
    }

    /**
     * @throws LogicException
     */
    public function validate(): void
    {
        if (empty($this->updateSources)) {
            throw new LogicException('List of updates is invalid.');
        }
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
     * @return ModuleSource[]
     */
    public function getUpdateSources(): array
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
}
