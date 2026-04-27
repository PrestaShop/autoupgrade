<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Manually remove the legacy controller. It has been deleted from the project but remain present while upgrading the module.
 *
 * @return bool
 */
function upgrade_module_5_0_3($module)
{
    $path = __DIR__ . '/../AdminSelfUpgrade.php';
    if (file_exists($path)) {
        $result = @unlink($path);
        if ($result !== true) {
            PrestaShopLogger::addLog('Could not delete deprecated controller AdminSelfUpgrade.php. ' . $result, 3);

            return false;
        }
    }

    return true;
}
