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
 * Migration of the "Configure > Advanced Parameters > Quick Access" page to Symfony.
 *
 * @see https://github.com/PrestaShop/PrestaShop/pull/41508
 *
 * The legacy AdminQuickAccesses tab used to be a hidden top-level tab (id_parent = -1).
 * The migration moves it under Advanced Parameters and keeps it inactive (hidden from the
 * menu, the page being reached through the quick access UI / feature flag), and introduces
 * the CRUD authorization roles required by the new Symfony grid.
 *
 * @return void
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_920_quick_access_tab()
{
    include_once __DIR__ . '/add_new_tab.php';

    // The AdminQuickAccesses tab already exists (legacy hidden tab), so add_new_tab_17() does not
    // re-create it: it only adds the CRUD authorization roles and copies the accesses from the
    // parent Advanced Parameters tab, mirroring the install fixtures.
    add_new_tab_17('AdminQuickAccesses', 'en:Quick Access', 0, false, 'AdminAdvancedParameters');

    $advancedParametersTabId = DbWrapper::getValue(
        'SELECT `id_tab` FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` = \'AdminAdvancedParameters\''
    );

    if (!empty($advancedParametersTabId)) {
        // Move the existing tab under Advanced Parameters and keep it inactive (reached through the quick access UI).
        DbWrapper::execute(
            'UPDATE `' . _DB_PREFIX_ . 'tab`
            SET `id_parent` = ' . (int) $advancedParametersTabId . ', `active` = 0
            WHERE `class_name` = \'AdminQuickAccesses\''
        );
    }
}
