<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

function ps_920_quick_access_tab()
{
    include_once __DIR__ . '/add_new_tab.php';

    $advancedParametersTabId = (int) DbWrapper::getValue(
        'SELECT `id_tab` FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` = \'AdminAdvancedParameters\''
    );

    // The AdminQuickAccesses tab already exists on every shop but, as a hidden orphan tab, it had
    // no authorization roles nor access grants. add_new_tab_17() is a no-op on the existing tab row,
    // but it creates the 4 missing ROLE_MOD_TAB_ADMINQUICKACCESSES_{CREATE,READ,UPDATE,DELETE} roles
    // and copies the access from the Advanced Parameters parent, making the tab manageable in
    // Configure > Advanced Parameters > Permissions.
    add_new_tab_17('AdminQuickAccesses', 'en:Quick Access', $advancedParametersTabId, false, 'AdminAdvancedParameters');

    // add_new_tab_17() never re-parents/deactivates an already-existing tab, so move the legacy
    // AdminQuickAccesses tab under Advanced Parameters and hide it from the sidebar navigation (#41508).
    if ($advancedParametersTabId) {
        DbWrapper::execute(
            'UPDATE `' . _DB_PREFIX_ . 'tab` SET `id_parent` = ' . $advancedParametersTabId . ', `active` = 0
             WHERE `class_name` = \'AdminQuickAccesses\''
        );
    }
}
