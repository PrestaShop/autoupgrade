<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
function removeAutoupgradePhpUnitFromFsDuringUpgrade(array $files)
{
    $files = array_reverse($files);
    foreach ($files as $file) {
        if (is_dir($file)) {
            $iterator = new FilesystemIterator($file, FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS);
            removeAutoupgradePhpUnitFromFsDuringUpgrade(iterator_to_array($iterator));
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
        $result = removeAutoupgradePhpUnitFromFsDuringUpgrade([$path]);
        if ($result !== true) {
            PrestaShopLogger::addLog('Could not delete PHPUnit from module. ' . $result, 3);

            return false;
        }
    }

    return true;
}
