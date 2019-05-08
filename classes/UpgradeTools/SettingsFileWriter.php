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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use PrestaShop\Module\AutoUpgrade\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use PrestaShop\Module\AutoUpgrade\LoggedEvent;

class SettingsFileWriter
{
    private $translator;

    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    public function migrateSettingsFile(LoggerInterface $logger)
    {
        if (class_exists('\PrestaShopBundle\Install\Upgrade')) {
            \PrestaShopBundle\Install\Upgrade::migrateSettingsFile(new LoggedEvent($logger));
        }
    }

    /**
     * @param string $filePath
     * @param array $data
     *
     * @throws UpgradeException
     */
    public function writeSettingsFile($filePath, $data)
    {
        if (!is_writable($filePath)) {
            throw new UpgradeException($this->translator->trans('Error when opening settings.inc.php file in write mode', array(), 'Modules.Autoupgrade.Admin'));
        }

        // Create backup file
        $filesystem = new Filesystem();
        $filesystem->copy($filePath, $filePath . '.bck');

        $fd = fopen($filePath, 'w');
        fwrite($fd, '<?php' . PHP_EOL);
        foreach ($data as $name => $value) {
            if (false === fwrite($fd, "define('$name', '{$this->checkString($value)}');" . PHP_EOL)) {
                throw new UpgradeException($this->translator->trans('Error when generating new settings.inc.php file.', array(), 'Modules.Autoupgrade.Admin'));
            }
        }
        fclose($fd);
    }

    public function checkString($string)
    {
        if (get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }
        if (!is_numeric($string)) {
            $string = addslashes($string);
            $string = str_replace(array("\n", "\r"), '', $string);
        }

        return $string;
    }
}
