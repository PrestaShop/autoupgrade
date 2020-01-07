<?php
/**
 * 2007-2020 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
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
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Removes files or directories.
 *
 * @param array $files An array of files to remove
 *
 * @return true|string True if everything goes fine, error details otherwise
 */
function removeFromFsDuringUpgrade(array $files)
{
    $files = array_reverse($files);
    foreach ($files as $file) {
        if (is_dir($file)) {
            $iterator = new FilesystemIterator($file, FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS);
            removeFromFsDuringUpgrade(iterator_to_array($iterator));
            if (!rmdir($file) && file_exists($file)) {
                return 'Deletion of directory ' . $file . 'failed';
            }
        } elseif (!unlink($file) && file_exists($file)) {
            return 'Deletion of file ' . $file . 'failed';
        }
    }
    
    return true;
}
/**
 * This upgrade file removes the folder vendor/phpunit, when added from a previous release installed on the shop.
 *
 * @return true|array
 */
function upgrade_module_4_10_1($module)
{
    $path = __DIR__ . '/../vendor/phpunit';
    if (file_exists($path)) {
        $result = removeFromFsDuringUpgrade(array($path));
        if ($result !== true) {
            PrestaShopLogger::addLog('Could not delete PHPUnit from module. ' . $result, 3);

            return false;
        }
    }

    return true;
}
