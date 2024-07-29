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

    /** @var int|null */
    private $moduleId;

    /** @var bool|null */
    private $moduleIsLocal;

    /** @var string|null */
    private $psVersion;

    private $addonsUrl = 'api.addons.prestashop.com';

    public function __construct(Translator $translator, Logger $logger)
    {
        $this->translator = $translator;
        $this->logger = $logger;
    }

    /**
     * @param array{id:string, name:string, is_local:true|null} $moduleInfos
     */
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

        $localModuleUsed = true;

        if ($this->moduleIsLocal) {
            $localModuleUsed = $this->downloadModuleFromLocalZip();
        }

        if (!$localModuleUsed) {
            $this->downloadModuleFromAddons();
        }

        if (filesize($this->zipFullPath) <= 300) {
            throw (new UpgradeException($this->translator->trans('[WARNING] An error occurred while downloading module %s , the received file is empty.', [$this->moduleName])))->setSeverity(UpgradeException::SEVERITY_WARNING);
        }
    }

    private function downloadModuleFromLocalZip(): bool
    {
        try {
            $localModuleZip = $this->getLocalModuleZipPath($this->moduleName);
            if (empty($localModuleZip)) {
                return false;
            }
            $filesystem = new Filesystem();
            $filesystem->copy($localModuleZip, $this->zipFullPath);
            unlink($localModuleZip);
            $this->logger->notice($this->translator->trans('Local module %s successfully copied.', [$this->moduleName]));
            return true;
        } catch (IOException $e) {
            $this->logger->notice($this->translator->trans('Can not found or copy local module %s. Trying to download it from Addons.', [$this->moduleName]));
        }
        return false;
    }

    /**
     * @throws UpgradeException
     */
    private function downloadModuleFromAddons(): void
    {
        $addonsUrl = extension_loaded('openssl')
            ? 'https://' . $this->addonsUrl
            : 'http://' . $this->addonsUrl;

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

    private function getLocalModuleZipPath(string $name): ?string
    {
        $autoUpgradeDir = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'autoupgrade';
        $module_zip = $autoUpgradeDir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $name . '.zip';

        if (file_exists($module_zip) && is_readable($module_zip)) {
            return $module_zip;
        }

        return null;
    }
}
