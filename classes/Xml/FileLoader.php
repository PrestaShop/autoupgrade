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

namespace PrestaShop\Module\AutoUpgrade\Xml;

use Configuration;
use PrestaShop\Module\AutoUpgrade\Tools14;
use PrestaShop\Module\AutoUpgrade\Upgrader;
use SimpleXMLElement;

class FileLoader
{
    const BASE_URL_MD5_FILES = 'https://api.prestashop.com/xml/md5/';
    const URL_CHANNELS_FILE = 'https://api.prestashop.com/xml/channel.xml';

    /** @var array<string, string> */
    public $version_md5 = [];

    /**
     * @return SimpleXMLElement|false
     */
    public function getXmlFile(string $xml_localfile, string $xml_remotefile, bool $refresh = false)
    {
        // @TODO : this has to be moved in autoupgrade.php > install method
        if (!is_dir(_PS_ROOT_DIR_ . '/config/xml')) {
            if (is_file(_PS_ROOT_DIR_ . '/config/xml')) {
                unlink(_PS_ROOT_DIR_ . '/config/xml');
            }
            mkdir(_PS_ROOT_DIR_ . '/config/xml', 0777);
        }
        if ($refresh || !file_exists($xml_localfile) || @filemtime($xml_localfile) < (time() - (3600 * Upgrader::DEFAULT_CHECK_VERSION_DELAY_HOURS))) {
            $xml_string = Tools14::file_get_contents($xml_remotefile, false, stream_context_create(['http' => ['timeout' => 10]]));
            $xml = @simplexml_load_string($xml_string);
            if ($xml !== false) {
                file_put_contents($xml_localfile, $xml_string);
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
     */
    public function getXmlMd5File(?string $version, bool $refresh = false)
    {
        if (isset($this->version_md5[$version])) {
            return @simplexml_load_file($this->version_md5[$version]);
        }

        return $this->getXmlFile(_PS_ROOT_DIR_ . '/config/xml/' . $version . '.xml', self::BASE_URL_MD5_FILES . $version . '.xml', $refresh);
    }

    /**
     * @return SimpleXMLElement|false
     */
    public function getXmlChannel(bool $refresh = false)
    {
        $xml = $this->getXmlFile(
            _PS_ROOT_DIR_ . '/config/xml/' . pathinfo(self::URL_CHANNELS_FILE, PATHINFO_BASENAME),
            self::URL_CHANNELS_FILE,
            $refresh
        );
        if ($refresh) {
            // TODO: Check this is triggered anywhere
            if (class_exists('Configuration', false)) {
                Configuration::updateValue('PS_LAST_VERSION_CHECK', time());
            }
        }

        return $xml;
    }
}
