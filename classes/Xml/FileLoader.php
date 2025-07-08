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

namespace PrestaShop\Module\AutoUpgrade\Xml;

use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use PrestaShop\Module\AutoUpgrade\Upgrader;
use SimpleXMLElement;
use Symfony\Component\Filesystem\Filesystem;

class FileLoader
{
    /** @var array<string, string> */
    private $version_md5 = [];
    /** @var Filesystem */
    private $filesystem;
    /** @var DistributionApiService */
    private $distributionApiService;

    public function __construct(Filesystem $filesystem, DistributionApiService $distributionApiService)
    {
        $this->filesystem = $filesystem;
        $this->distributionApiService = $distributionApiService;
    }

    /**
     * @return SimpleXMLElement|false
     */
    public function getXmlFile(string $xml_localfile, string $xml_remotefile, bool $refresh = false)
    {
        // @TODO : this has to be moved in autoupgrade.php > install method
        if (!is_dir(_PS_ROOT_DIR_ . '/config/xml')) {
            if (is_file(_PS_ROOT_DIR_ . '/config/xml')) {
                $this->filesystem->remove(_PS_ROOT_DIR_ . '/config/xml');
            }
            $this->filesystem->mkdir(_PS_ROOT_DIR_ . '/config/xml');
        }
        // End TODO

        if ($refresh || !$this->filesystem->exists($xml_localfile) || @filemtime($xml_localfile) < (time() - (3600 * Upgrader::DEFAULT_CHECK_VERSION_DELAY_HOURS))) {
            $xml_string = file_get_contents($xml_remotefile, false, stream_context_create(['http' => ['timeout' => 10]]));
            $xml = @simplexml_load_string($xml_string);
            if ($xml !== false) {
                $this->filesystem->dumpFile($xml_localfile, $xml_string);
            }
        } else {
            $xml = @simplexml_load_file($xml_localfile);
        }

        return $xml;
    }

    /**
     * return xml containing the list of all default PrestaShop files for version $version,
     * and their respective md5sum.
     *
     * @return SimpleXMLElement|false if error
     *
     * @throws DistributionApiException
     */
    public function getXmlMd5File(?string $version, bool $refresh = false)
    {
        if (isset($this->version_md5[$version])) {
            return @simplexml_load_file($this->version_md5[$version]);
        }

        $releaseInfo = $this->distributionApiService->getRelease($version);

        if ($releaseInfo === null) {
            return false;
        }

        $releaseXmlRemoteFile = $releaseInfo->getXmlDownloadUrl();

        return $this->getXmlFile(_PS_ROOT_DIR_ . '/config/xml/' . $version . '.xml', $releaseXmlRemoteFile, $refresh);
    }

    public function addXmlMd5File(string $version, string $path): void
    {
        $this->version_md5[$version] = $path;
    }
}
