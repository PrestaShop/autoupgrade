<?php

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
