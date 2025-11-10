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

namespace PrestaShop\Module\AutoUpgrade\Services;

use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\MarketplaceModule;
use PrestaShop\Module\AutoUpgrade\Xml\FileLoader;

class MarketplaceService
{
    const ADDONS_API_URL = 'https://api.addons.prestashop.com';

    /** @var FileLoader */
    private $fileLoader;
    /** @var string */
    private $prestashopRootFolder;

    public function __construct(FileLoader $fileLoader, string $prestashopRootFolder)
    {
        $this->prestashopRootFolder = $prestashopRootFolder;
        $this->fileLoader = $fileLoader;
    }

    /**
     * @throws DistributionApiException
     *
     * @param string $endPoint
     *
     * @return MarketplaceModule[]|null
     */
    public function listModule(string $prestashopVersion)
    {
         $postData = http_build_query([
            'action' => 'native',
            'iso_code' => 'all',
            'method' => 'listing',
            'version' => $prestashopVersion,
        ]);

        $xml = $this->fileLoader->getXmlFile(
            $this->prestashopRootFolder . '/config/xml/' . $prestashopVersion . '_modules_native_addons.xml',
            self::ADDONS_API_URL . '/?' . $postData
        );

        if ($xml === false) {
            return;
        }

        $modules = [];

        foreach ($xml as $moduleInXml) {
            $id = (int) $moduleInXml->id;
            $modules[$id] = new MarketplaceModule(
                $id,
                (string) $moduleInXml->name,
                (string) $moduleInXml->compatibility->from,
                (string) $moduleInXml->compatibility->to,
                (string) $moduleInXml->version,
            );
        }

        return $modules;
    }
}
