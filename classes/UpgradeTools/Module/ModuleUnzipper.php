<?php

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

use LogicException;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use PrestaShop\Module\AutoUpgrade\ZipAction;

class ModuleUnzipper
{
    /** @var Translator */
    private $translator;

    /** @var Logger */
    private $logger;

    /** @var ZipAction|null */
    private $zipAction;

    /** @var string|null */
    private $zipFullPath;

    /** @var string|null */
    private $modulesPath;

    /** @var string|null */
    private $moduleName;

    public function __construct(Translator $translator, Logger $logger)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->zipAction = null;
        $this->zipFullPath = null;
        $this->modulesPath = null;
        $this->moduleName = null;
    }

    public function setUnzipContext(ZipAction $zipAction, string $zipFullPath, string $modulesPath, string $moduleName): void
    {
        $this->zipAction = $zipAction;
        $this->zipFullPath = $zipFullPath;
        $this->modulesPath = $modulesPath;
        $this->moduleName = $moduleName;
    }

    /**
     * @throws LogicException|UpgradeException
     */
    public function unzipModule(): void
    {
        if ($this->zipAction === null || $this->zipFullPath === null || $this->modulesPath === null || $this->moduleName === null) {
            throw (new LogicException('Module unzip context is empty, please run setUnzipContext() first.'));
        }

        if (!$this->zipAction->extract($this->zipFullPath, $this->modulesPath)) {
            throw (new UpgradeException($this->translator->trans('[WARNING] Error when trying to extract module %s.', [$this->moduleName])))->setSeverity(UpgradeException::SEVERITY_WARNING);
        }
        if (file_exists($this->zipFullPath)) {
            unlink($this->zipFullPath);
        }
    }
}
