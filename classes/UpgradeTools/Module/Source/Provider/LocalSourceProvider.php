<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider;

use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;
use ZipArchive;

/*
 * Get the list of module zips in admin/autoupgrade/modules
 * These zips will be used to upgrade related modules instead of using distant zips on addons
 */
class LocalSourceProvider extends AbstractModuleSourceProvider
{
    /** @var string */
    private $sourceFolder;

    /** @var FileStorage */
    private $fileConfigurationStorage;

    public function __construct(string $sourceFolder, FileStorage $fileConfigurationStorage)
    {
        $this->sourceFolder = $sourceFolder;
        $this->fileConfigurationStorage = $fileConfigurationStorage;
    }

    public function warmUp(): void
    {
        if ($this->fileConfigurationStorage->exists(UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_LOCAL)) {
            $this->localModuleZips = $this->fileConfigurationStorage->load(UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_LOCAL);

            return;
        }

        $this->localModuleZips = [];

        $zipFiles = glob($this->sourceFolder . DIRECTORY_SEPARATOR . '*.zip');

        if ($zipFiles === false) {
            return;
        }

        foreach ($zipFiles as $zipFile) {
            // The archive must be named as the module, and nothing else.
            $moduleName = pathinfo($zipFile, PATHINFO_FILENAME);

            $this->localModuleZips[] = new ModuleSource(
                $moduleName,
                $this->getVersionInZip($zipFile, $moduleName),
                $zipFile,
                true
            );
        }

        $this->fileConfigurationStorage->save($this->localModuleZips, UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_LOCAL);
    }

    private function getVersionInZip(string $zipFilePath, string $moduleName): ?string
    {
        $version = null;
        $zipArchive = new ZipArchive();
        $zipArchive->open($zipFilePath);

        $xml = simplexml_load_string($zipArchive->getFromName($moduleName . '/config.xml'));

        if (!$xml) {
            $zipArchive->close();

            return null;
        }

        $version = (string) $xml->version;

        $zipArchive->close();

        return $version;
    }
}
