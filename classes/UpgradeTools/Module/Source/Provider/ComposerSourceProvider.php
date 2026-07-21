<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider;

use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Services\ComposerService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;

/*
 * Gets the modules bundled with a PrestaShop release by reading its composer.lock file.
 */
class ComposerSourceProvider extends AbstractModuleSourceProvider
{
    /** @var string */
    private $prestaShopReleaseFolder;

    /** @var ComposerService */
    private $composerService;

    /** @var FileStorage */
    private $fileConfigurationStorage;

    public function __construct(string $prestaShopReleaseFolder, ComposerService $composerService, FileStorage $fileConfigurationStorage)
    {
        $this->prestaShopReleaseFolder = $prestaShopReleaseFolder;
        $this->composerService = $composerService;
        $this->fileConfigurationStorage = $fileConfigurationStorage;
    }

    public function warmUp(): void
    {
        if ($this->fileConfigurationStorage->exists(UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_COMPOSER)) {
            $this->localModuleZips = $this->fileConfigurationStorage->load(UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_COMPOSER);

            return;
        }

        $this->localModuleZips = [];

        $modulesList = $this->composerService->getModulesInComposerLock($this->prestaShopReleaseFolder . '/composer.lock');

        foreach ($modulesList as $module) {
            $this->localModuleZips[] = new ModuleSource(
                $module['name'],
                $module['version'],
                $this->prestaShopReleaseFolder . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module['name'],
                false
            );
        }

        $this->fileConfigurationStorage->save($this->localModuleZips, UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_COMPOSER);
    }
}
