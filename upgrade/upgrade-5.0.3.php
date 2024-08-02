<?php
/**
 * 2007-2022 PrestaShop.
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
 * @copyright 2007-2022 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
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
