<?php

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

use LogicException;

class ModuleDownloaderContext
{
    /** @var string */
    private $zipFullPath;

    /** @var string */
    private $moduleName;

    /** @var int */
    private $moduleId;

    /** @var bool */
    private $moduleIsLocal = false;

    /**
     * @param array{id:string, name:string, is_local:true|null} $moduleInfos
     */
    public function __construct(string $zipFullPath, array $moduleInfos)
    {
        $this->zipFullPath = $zipFullPath;
        $this->moduleName = $moduleInfos['name'];
        $this->moduleId = (int) $moduleInfos['id'];
        $this->moduleIsLocal = (bool) ($moduleInfos['is_local'] ?? false);
        $this->validate();
    }

    /**
     * @throws LogicException
     */
    private function validate(): void
    {
        if (empty($this->zipFullPath)) {
            throw new LogicException('Path to zip file is invalid.');
        }
        if (empty($this->moduleName)) {
            throw new LogicException('Module name is invalid.');
        }
        if (empty($this->moduleId)) {
            throw new LogicException('Module ID is invalid.');
        }
    }

    public function getZipFullPath(): string
    {
        return $this->zipFullPath;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getModuleId(): int
    {
        return $this->moduleId;
    }

    public function getModuleIsLocal(): bool
    {
        return $this->moduleIsLocal;
    }
}
