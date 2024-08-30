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

namespace PrestaShop\Module\AutoUpgrade;

use PrestaShop\Module\AutoUpgrade\Xml\FileLoader;
use SimpleXMLElement;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class Upgrader
{
    const DEFAULT_CHECK_VERSION_DELAY_HOURS = 12;
    const DEFAULT_CHANNEL = 'minor';
    const DEFAULT_FILENAME = 'prestashop.zip';

    const ADDONS_API_URL = 'api.addons.prestashop.com';

    /**
     * @var bool contains true if last version is not installed
     */
    private $need_upgrade = false;

    /** @var string */
    public $version_name;
    /** @var ?string */
    public $version_num;
    /**
     * @var string contains the url to download the file
     */
    public $link;
    /** @var string */
    public $autoupgrade_last_version;
    /** @var string */
    public $autoupgrade_module_link;
    /** @var string */
    public $changelog;
    /** @var bool */
    public $available;
    /** @var string */
    public $md5;

    /** @var string */
    public $channel = '';
    /** @var string */
    public $branch = '';

    /** @var string */
    protected $currentPsVersion;
    /**
     * @var FileLoader
     */
    protected $fileLoader;

    public function __construct(string $version, FileLoader $fileLoader)
    {
        $this->currentPsVersion = $version;
        $this->fileLoader = $fileLoader;
    }

    /**
     * downloadLast download the last version of PrestaShop and save it in $dest/$filename.
     *
     * @param string $dest directory where to save the file
     * @param string $filename new filename
     *
     * @TODO ftp if copy is not possible (safe_mode for example)
     */
    public function downloadLast(string $dest, string $filename = 'prestashop.zip'): bool
    {
        if (empty($this->link)) {
            $this->checkPSVersion();
        }

        $destPath = realpath($dest) . DIRECTORY_SEPARATOR . $filename;

        try {
            $filesystem = new Filesystem();
            $filesystem->copy($this->link, $destPath);
        } catch (IOException $e) {
            // If the Symfony filesystem failed, we can try with
            // the legacy method which uses curl.
            Tools14::copy($this->link, $destPath);
        }

        return is_file($destPath);
    }

    public function isLastVersion(): bool
    {
        if (empty($this->link)) {
            $this->checkPSVersion();
        }

        return !$this->need_upgrade;
    }

    /**
     * checkPSVersion ask to prestashop.com if there is a new version. return an array if yes, false otherwise.
     *
     * @param bool $refresh if set to true, will force to download channel.xml
     * @param string[] $array_no_major array of channels which will return only the immediate next version number
     *
     * @return array{'name':string,'link':string}|false
     */
    public function checkPSVersion(bool $refresh = false, array $array_no_major = ['minor'])
    {
        // if we use the autoupgrade process, we will never refresh it
        // except if no check has been done before
        $feed = $this->fileLoader->getXmlChannel($refresh);

        // channel hierarchy :
        // if you follow private, you follow stable release
        // if you follow rc, you also follow stable
        // if you follow beta, you also follow rc
        // et caetera
        $followed_channels = [];
        $followed_channels[] = $this->channel;
        switch ($this->channel) {
        case 'alpha':
            $followed_channels[] = 'beta';
            // no break
        case 'beta':
            $followed_channels[] = 'rc';
            // no break
        case 'rc':
            $followed_channels[] = 'stable';
            // no break
        case 'minor':
        case 'major':
        case 'private':
            $followed_channels[] = 'stable';
        }

        if ($feed) {
            $this->autoupgrade_last_version = (string) $feed->autoupgrade->last_version;
            $this->autoupgrade_module_link = (string) $feed->autoupgrade->download->link;

            foreach ($feed->channel as $channel) {
                $channel_available = (string) $channel['available'];

                $channel_name = (string) $channel['name'];
                // stable means major and minor
                // boolean algebra
                // skip if one of theses props are true:
                // - "stable" in xml, "minor" or "major" in configuration
                // - channel in xml is not channel in configuration
                if (!(in_array($channel_name, $followed_channels))) {
                    continue;
                }
                // now we are on the correct channel (minor, major, ...)
                foreach ($channel as $branch) {
                    // branch name = which version
                    $branch_name = (string) $branch['name'];
                    // if channel is "minor" in configuration, do not allow something else than current branch
                    // otherwise, allow superior or equal
                    if (
                        (in_array($this->channel, $followed_channels)
                        && version_compare($branch_name, $this->branch, '>='))
                    ) {
                        // skip if $branch->num is inferior to a previous one, skip it
                        if ($this->version_num !== null && version_compare((string) $branch->num, $this->version_num, '<')) {
                            continue;
                        }
                        // also skip if previous loop found an available upgrade and current is not
                        if ($this->available && !($channel_available && (string) $branch['available'])) {
                            continue;
                        }
                        // also skip if chosen channel is minor, and xml branch name is superior to current
                        if (in_array($this->channel, $array_no_major) && version_compare($branch_name, $this->branch, '>')) {
                            continue;
                        }
                        $this->version_name = (string) $branch->name;
                        $this->version_num = (string) $branch->num;
                        $this->link = (string) $branch->download->link;
                        $this->md5 = (string) $branch->download->md5;
                        $this->changelog = (string) $branch->changelog;
                        if (extension_loaded('openssl')) {
                            $this->link = str_replace('http://', 'https://', $this->link);
                            $this->changelog = str_replace('http://', 'https://', $this->changelog);
                        }
                        $this->available = $channel_available && (string) $branch['available'];
                    }
                }
            }
        } else {
            return false;
        }
        // retro-compatibility :
        // return array(name,link) if you don't use the last version
        // false otherwise
        if ($this->version_num !== null && version_compare($this->currentPsVersion, $this->version_num, '<')) {
            $this->need_upgrade = true;

            return ['name' => $this->version_name, 'link' => $this->link];
        } else {
            return false;
        }
    }

    /**
     * delete the file /config/xml/$version.xml if exists.
     */
    public function clearXmlMd5File(string $version): bool
    {
        if (file_exists(_PS_ROOT_DIR_ . '/config/xml/' . $version . '.xml')) {
            return unlink(_PS_ROOT_DIR_ . '/config/xml/' . $version . '.xml');
        }

        return true;
    }

    /**
     * use the addons api to get xml files.
     *
     * @param string $postData
     *
     * @return SimpleXMLElement|false
     */
    public function getApiAddons(string $xml_localfile, string $postData, bool $refresh = false)
    {
        if (!is_dir(_PS_ROOT_DIR_ . '/config/xml')) {
            if (is_file(_PS_ROOT_DIR_ . '/config/xml')) {
                unlink(_PS_ROOT_DIR_ . '/config/xml');
            }
            mkdir(_PS_ROOT_DIR_ . '/config/xml');
        }
        if ($refresh || !file_exists($xml_localfile) || @filemtime($xml_localfile) < (time() - (3600 * self::DEFAULT_CHECK_VERSION_DELAY_HOURS))) {
            $protocolsList = ['https://' => 443, 'http://' => 80];
            if (!extension_loaded('openssl')) {
                unset($protocolsList['https://']);
            }
            // Make the request
            $opts = [
                'http' => [
                    'method' => 'POST',
                    'content' => $postData,
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'timeout' => 10,
                ], ];
            $context = stream_context_create($opts);
            $xml = false;
            foreach ($protocolsList as $protocol => $port) {
                $xml_string = Tools14::file_get_contents($protocol . self::ADDONS_API_URL, false, $context);
                if ($xml_string) {
                    $xml = @simplexml_load_string($xml_string);
                    break;
                }
            }
            if ($xml !== false) {
                file_put_contents($xml_localfile, $xml_string);
            }
        } else {
            $xml = @simplexml_load_file($xml_localfile);
        }

        return $xml;
    }
}
