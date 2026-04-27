<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

function ps_920_business_entities_tabs()
{
    include_once __DIR__ . '/add_new_tab.php';

    $adminBusinessEntityId = add_new_tab_17(
        'AdminBusinessEntity',
        'en:Business Entity',
        2,
        true
    );
    update_module_tab($adminBusinessEntityId, 'Business Entity', 'Admin.Navigation.Menu', 'business_center');

    $adminBusinessEntitiesId = add_new_tab_17(
        'AdminBusinessEntities',
        'en:Business Entities',
        $adminBusinessEntityId,
        true
    );
    update_module_tab($adminBusinessEntitiesId, 'Business Entities', 'Admin.Navigation.Menu', '', 'admin_business_entities_list');

    $adminCustomersB2BId = add_new_tab_17(
        'AdminCustomersB2B',
        'en:Customers B2B',
        $adminBusinessEntityId,
        true
    );
    update_module_tab($adminCustomersB2BId, 'Customers B2B', 'Admin.Navigation.Menu', '', 'admin_customer_b2b_list');
}

function update_module_tab(
    $id_tab,
    $wording = '',
    $wording_domain = 'Admin.Navigation.Menu',
    $icon = '',
    $route_name = '',
    $active = 0,
    $enabled = 1
) {
    $sql = sprintf(
        'UPDATE `' . _DB_PREFIX_
        . 'tab` SET `active`=%d, `enabled`=%d, `wording`="%s", `wording_domain`="%s", `icon`="%s", `route_name`="%s" WHERE `id_tab` = %d',
        $active,
        $enabled,
        $wording,
        $wording_domain,
        $icon,
        $route_name,
        $id_tab
    );
    DbWrapper::execute($sql);
}
