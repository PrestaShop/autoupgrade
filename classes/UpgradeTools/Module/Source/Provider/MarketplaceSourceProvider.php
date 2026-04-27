<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\Provider;

use PrestaShop\Module\AutoUpgrade\Parameters\FileStorage;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Services\MarketplaceService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSource;
use PrestaShop\Module\AutoUpgrade\Xml\FileLoader;

/*
 * Get the updates from the marketplace API, based on the details stored in "native" XML feed.
 */
class MarketplaceSourceProvider extends AbstractModuleSourceProvider
{
    /** @var FileLoader */
    private $fileLoader;

    /** @var FileStorage */
    private $fileConfigurationStorage;

    /** @var string */
    private $targetVersionOfPrestaShop;

    /** @var string */
    private $prestashopRootFolder;

    public function __construct(string $targetVersionOfPrestaShop, string $prestashopRootFolder, FileLoader $fileLoader, FileStorage $fileConfigurationStorage)
    {
        $this->targetVersionOfPrestaShop = $targetVersionOfPrestaShop;
        $this->prestashopRootFolder = $prestashopRootFolder;
        $this->fileLoader = $fileLoader;
        $this->fileConfigurationStorage = $fileConfigurationStorage;
    }

    public function warmUp(): void
    {
        if ($this->fileConfigurationStorage->exists(UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_MARKETPLACE_API)) {
            $this->localModuleZips = $this->fileConfigurationStorage->load(UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_MARKETPLACE_API);

            return;
        }

        $postData = http_build_query([
            'action' => 'native',
            'iso_code' => 'all',
            'method' => 'listing',
            'version' => $this->targetVersionOfPrestaShop,
        ]);

        $xml = $this->fileLoader->getXmlFile(
            $this->prestashopRootFolder . '/config/xml/modules_native_addons.xml',
            MarketplaceService::ADDONS_API_URL . '/?' . $postData
        );

        if ($xml === false) {
            return;
        }

        $this->localModuleZips = [];

        foreach ($xml as $moduleInXml) {
            $this->localModuleZips[] = new ModuleSource(
                (string) $moduleInXml->name,
                (string) $moduleInXml->version,
                MarketplaceService::ADDONS_API_URL . '/?' . http_build_query([
                    'id_module' => (string) $moduleInXml->id,
                    'method' => 'module',
                    'version' => $this->targetVersionOfPrestaShop,
                ]),
                true
            );
        }

        $this->fileConfigurationStorage->save($this->localModuleZips, UpgradeFileNames::MODULE_SOURCE_PROVIDER_CACHE_MARKETPLACE_API);
    }
}
