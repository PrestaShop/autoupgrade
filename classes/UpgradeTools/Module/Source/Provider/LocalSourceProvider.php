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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider;

use PrestaShop\Module\AutoUpgrade\Parameters\FileConfigurationStorage;
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

    /** @var FileConfigurationStorage */
    private $fileConfigurationStorage;

    public function __construct(string $sourceFolder, FileConfigurationStorage $fileConfigurationStorage)
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
