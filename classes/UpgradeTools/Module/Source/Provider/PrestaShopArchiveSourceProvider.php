<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider;

use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Services\ComposerService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;

/*
 * Gets the modules added in the PrestaShop release, in the modules/ folder but were forgotten in the composer.json file.
 */
class PrestaShopArchiveSourceProvider extends AbstractModuleSourceProvider
{
    /** @var string */
    private $prestaShopReleaseFolder;

    /** @var ComposerService */
    private $composerService;

    /** @var FileStorage */
    private $fileConfigurationStorage;

    /** @var ModuleAdapter */
    private $moduleAdapter;

    public function __construct(string $prestaShopReleaseFolder, ComposerService $composerService, FileStorage $fileConfigurationStorage, ModuleAdapter $moduleAdapter)
    {
        $this->prestaShopReleaseFolder = $prestaShopReleaseFolder;
        $this->composerService = $composerService;
        $this->fileConfigurationStorage = $fileConfigurationStorage;
        $this->moduleAdapter = $moduleAdapter;
    }

    public function warmUp(): void
    {
        if ($this->fileConfigurationStorage->exists(UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_PRESTASHOP_ARCHIVE)) {
            $this->localModuleZips = $this->fileConfigurationStorage->load(UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_PRESTASHOP_ARCHIVE);

            return;
        }

        $this->localModuleZips = [];

        /** @var string[] */
        $modulesNamesInComposer = array_column(
            $this->composerService->getModulesInComposerLock($this->prestaShopReleaseFolder . '/composer.lock'),
            'name'
        );

        /** @var string[] */
        $modulesNamesInModulesFolderOfRelease = scandir($this->prestaShopReleaseFolder . DIRECTORY_SEPARATOR . 'modules') ?: [];
        $modulesList = array_diff($modulesNamesInModulesFolderOfRelease, $modulesNamesInComposer);

        foreach ($modulesList as $module) {
            $path = $this->prestaShopReleaseFolder . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module;
            if (!$this->moduleAdapter->isFolderContainingModule($path)) {
                continue;
            }

            $version = $this->moduleAdapter->getVersionFromConfigXmlInFolder($path);
            if (!$version) {
                continue;
            }

            $this->localModuleZips[] = new ModuleSource(
                $module,
                $version,
                $path,
                false
            );
        }

        $this->fileConfigurationStorage->save($this->localModuleZips, UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_PRESTASHOP_ARCHIVE);
    }
}
