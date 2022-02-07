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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use PrestaShop\Module\AutoUpgrade\Log\LoggerInterface;
use PrestaShop\Module\AutoUpgrade\LoggedEvent;
use PrestaShop\Module\AutoUpgrade\UpgradeException;
use Symfony\Component\Filesystem\Filesystem;

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
            throw new UpgradeException($this->translator->trans('Error when opening settings.inc.php file in write mode', [], 'Modules.Autoupgrade.Admin'));
        }

        // Create backup file
        $filesystem = new Filesystem();
        $filesystem->copy($filePath, $filePath . '.bck');

        $fd = fopen($filePath, 'w');
        fwrite($fd, '<?php' . PHP_EOL);
        foreach ($data as $name => $value) {
            if (false === fwrite($fd, "define('$name', '{$this->checkString($value)}');" . PHP_EOL)) {
                throw new UpgradeException($this->translator->trans('Error when generating new settings.inc.php file.', [], 'Modules.Autoupgrade.Admin'));
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
            $string = str_replace(["\n", "\r"], '', $string);
        }

        return $string;
    }
}
