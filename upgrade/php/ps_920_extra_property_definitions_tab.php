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

function ps_920_extra_property_definitions_tab()
{
    include_once __DIR__ . '/add_new_tab.php';

    $adminExtraPropertyDefinitionsId = add_new_tab_17(
        'AdminExtraPropertyDefinitions',
        'en:Extra Properties',
        0,
        true,
        'AdminAdvancedParameters'
    );

    if (empty($adminExtraPropertyDefinitionsId)) {
        return;
    }

    DbWrapper::execute(
        'UPDATE `' . _DB_PREFIX_ . 'tab` 
        SET `active`=1, `enabled`=1, `wording`="Extra Properties", `wording_domain`="Admin.Navigation.Menu", `icon`="", `route_name`="admin_extra_property_definitions_index" 
        WHERE `id_tab` = ' . (int) $adminExtraPropertyDefinitionsId
    );
}
