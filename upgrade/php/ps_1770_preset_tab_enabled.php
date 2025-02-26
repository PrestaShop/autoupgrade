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

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * Preset enabled new column in tabs to true for all (except for disabled modules)
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_1770_preset_tab_enabled()
{
    //First set all tabs enabled
    $result = DbWrapper::execute(
        'UPDATE `' . _DB_PREFIX_ . 'tab` SET `enabled` = 1'
    );

    //Then search for inactive modules and disable their tabs
    $inactiveModules = DbWrapper::executeS(
        'SELECT `name` FROM `' . _DB_PREFIX_ . 'module` WHERE `active` != 1'
    );
    $moduleNames = [];
    foreach ($inactiveModules as $inactiveModule) {
        $moduleNames[] = '"' . $inactiveModule['name'] . '"';
    }
    if (count($moduleNames) > 0) {
        $result &= DbWrapper::execute(
            'UPDATE `' . _DB_PREFIX_ . 'tab` SET `enabled` = 0 WHERE `module` IN (' . implode(',', $moduleNames) . ')'
        );
    }

    return $result;
}
