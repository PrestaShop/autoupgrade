<?php

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

use LogicException;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\Tools14;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class ModuleDownloader
{
    /** @var Translator */
    private $translator;

    /** @var Logger */
    private $logger;

    /** @var string|null */
    private $zipFullPath;

    /** @var string|null */
    private $moduleName;

    /** @var int */
    private $moduleId;

    /** @var bool|null */
    private $moduleIsLocal;

    /** @var string|null */
    private $psVersion;

    /** @var bool */
    private $localModuleUsed;

    public function __construct(Translator $translator, Logger $logger)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->zipFullPath = null;
        $this->moduleName = null;
        $this->moduleId = null;
        $this->moduleIsLocal = null;
        $this->psVersion = null;
        $this->localModuleUsed = false;
    }

    public function setDownloadContext(string $zipFullPath, array $moduleInfos, string $psVersion): void
    {
        $this->moduleName = $moduleInfos['name'];
        $this->zipFullPath = $zipFullPath;
        $this->moduleId = (int) $moduleInfos['id'];
        $this->moduleIsLocal = $moduleInfos['is_local'] ?? false;
        $this->psVersion = $psVersion;
    }

    /**
     * @throws LogicException|UpgradeException
     */
    public function downloadModule(): void
    {
        if ($this->zipFullPath === null || $this->moduleName === null || $this->moduleId === null || $this->psVersion === null) {
            throw (new LogicException('Module download context is empty, please run setDownloadContext() first.'));
        }

        if ($this->moduleIsLocal) {
            $this->downloadModuleFromLocalZip();
        }

        if (!$this->localModuleUsed) {
            $this->downloadModuleFromAddons();
        }

        if (filesize($this->zipFullPath) <= 300) {
            throw (new UpgradeException($this->translator->trans('[ERROR] No response from Addons server.')))->setSeverity(UpgradeException::SEVERITY_WARNING);
            unlink($this->zipFullPath);
        }
    }

    private function downloadModuleFromLocalZip(): void
    {
        try {
            $localModuleZip = $this->getLocalModuleZip($this->moduleName);
            if (!empty($local_module_zip)) {
                $filesystem = new Filesystem();
                $filesystem->copy($local_module_zip, $this->zipFullPath);
                unlink($localModuleZip);
                $this->localModuleUsed = true;
            }
            $this->logger->notice($this->translator->trans('Local module %s successfully copied.', [$this->moduleName]));
        } catch (IOException $e) {
            $this->logger->notice($this->translator->trans('Can not found or copy local module %s. Trying to download it from Addons.', [$this->moduleName]));
        }
    }

    /**
     * @throws UpgradeException
     */
    private function downloadModuleFromAddons(): void
    {
        $addonsUrl = extension_loaded('openssl')
            ? 'https://api.addons.prestashop.com'
            : 'http://api.addons.prestashop.com';

        // Make the request
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'content' => 'version=' . $this->psVersion . '&method=module&id_module=' . $this->moduleId,
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'timeout' => 10,
            ],
        ]);

        // file_get_contents can return false if https is not supported (or warning)
        $content = Tools14::file_get_contents($addonsUrl, false, $context);
        if (empty($content) || substr($content, 5) == '<?xml') {
            throw (new UpgradeException($this->translator->trans('[WARNING] No response from Addons server.')))->setSeverity(UpgradeException::SEVERITY_WARNING);
        }

        if (false === (bool) file_put_contents($this->zipFullPath, $content)) {
            throw (new UpgradeException($this->translator->trans('[WARNING] Unable to write module %s\'s zip file in temporary directory.', [$this->moduleName])))->setSeverity(UpgradeException::SEVERITY_WARNING);
        }

        $this->logger->notice($this->translator->trans('Module %s has been successfully downloaded from Addons.', [$this->moduleName]));
    }

    private function getLocalModuleZip(string $name): ?string
    {
        $autoUpgradeDir = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'autoupgrade';
        $module_zip = $autoUpgradeDir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $name . '.zip';

        if (file_exists($module_zip) && is_readable($module_zip)) {
            return $module_zip;
        }

        return null;
    }
}
