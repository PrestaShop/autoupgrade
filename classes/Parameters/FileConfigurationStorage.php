<?php
/*
 * 2007-2018 PrestaShop
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * 
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters;

use PrestaShop\Module\AutoUpgrade\Tools14;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class FileConfigurationStorage
{
    /**
     * UpgradeConfiguration loader.
     *
     * @param string $configFilePath
     * @return mixed or array() as default value
     */
    public static function load($configFilePath = '')
    {
        $config = array();

        if (!empty($configFilePath) && file_exists($configFilePath)) {
            $content = Tools14::file_get_contents($configFilePath);
            $config = @unserialize(base64_decode($content));
        }

        return $config;
    }

    /**
     *
     * @param mixed $config
     * @param string $configFilePath Destination path of the onfig file
     * @return boolean
     */
    public static function save($config, $configFilePath)
    {
        try {
            (new Filesystem)->dumpFile($configFilePath, base64_encode(serialize($config)));
            return true;
        } catch (IOException $e) {
            // TODO: $e needs to be logged
            return false;
        }
    }
}