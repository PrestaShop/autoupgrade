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

class ModuleMigrationContext
{
    /** @var \Module */
    private $moduleInstance;

    /** @var string */
    private $moduleName;

    /** @var string */
    private $dbVersion;

    /** @var string */
    private $upgradeFilesRootPath;

    /** @var string */
    private $localVersion;

    /** @var string[]|null */
    private $migrationFiles;

    public function __construct(\Module $moduleInstance, ?string $dbVersion)
    {
        $this->moduleInstance = $moduleInstance;

        $moduleName = $moduleInstance->name;

        $this->moduleName = $moduleName;
        $this->upgradeFilesRootPath = _PS_MODULE_DIR_ . $moduleName . DIRECTORY_SEPARATOR . 'upgrade';

        $this->localVersion = $this->moduleInstance->version;
        $this->dbVersion = $dbVersion ?? '0';
    }

    public function getModuleInstance(): \Module
    {
        return $this->moduleInstance;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getUpgradeFilesRootPath(): string
    {
        return $this->upgradeFilesRootPath;
    }

    public function getLocalVersion(): string
    {
        return $this->localVersion;
    }

    public function getDbVersion(): string
    {
        return $this->dbVersion;
    }

    /**
     * @param string[] $migrationFiles
     */
    public function setMigrationFiles(array $migrationFiles): void
    {
        $this->migrationFiles = $migrationFiles;
    }

    /**
     * @return string[]|null
     */
    public function getMigrationFiles(): ?array
    {
        return $this->migrationFiles;
    }
}
