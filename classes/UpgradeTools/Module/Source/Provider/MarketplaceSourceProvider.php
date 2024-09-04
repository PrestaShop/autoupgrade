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
use PrestaShop\Module\AutoUpgrade\Xml\FileLoader;

/*
 * Get the updates from the marketplace API, based on the details stored in "native" XML feed.
 */
class MarketplaceSourceProvider extends AbstractModuleSourceProvider
{
    const ADDONS_API_URL = 'https://api.addons.prestashop.com';

    /** @var FileLoader */
    private $fileLoader;

    /** @var FileConfigurationStorage */
    private $fileConfigurationStorage;

    /** @var string */
    private $targetVersionOfPrestaShop;

    /** @var string */
    private $prestashopRootFolder;

    public function __construct(string $targetVersionOfPrestaShop, string $prestashopRootFolder, FileLoader $fileLoader, FileConfigurationStorage $fileConfigurationStorage)
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
            self::ADDONS_API_URL . '/?' . $postData
        );

        if ($xml === false) {
            return;
        }

        $this->localModuleZips = [];

        foreach ($xml as $moduleInXml) {
            $this->localModuleZips[] = new ModuleSource(
                (string) $moduleInXml->name,
                (string) $moduleInXml->version,
                self::ADDONS_API_URL . '/?' . http_build_query([
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
