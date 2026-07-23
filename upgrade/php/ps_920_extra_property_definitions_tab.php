<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
