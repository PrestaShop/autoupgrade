<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Manually remove the dashboardZoneOne hook.
 *
 * @return bool
 */
function upgrade_module_6_0_0($module)
{
    if (!$module->unregisterHook('dashboardZoneOne')) {
        return false;
    }

    // Update the 'AdminSelfUpgrade' tab configuration
    $id_tab = \Tab::getIdFromClassName('AdminSelfUpgrade');
    if ($id_tab) {
        $tab = new \Tab($id_tab);
    } else {
        // If the tab doesn't exist, create it
        $tab = new \Tab();
        $tab->class_name = 'AdminSelfUpgrade';
        $tab->module = 'autoupgrade';
    }

    $tab->id_parent = (int) \Tab::getIdFromClassName('CONFIGURE');
    $tab->icon = 'upgrade';

    foreach (\Language::getLanguages(false) as $lang) {
        $tab->name[(int) $lang['id_lang']] = '1-Click Upgrade';
    }

    if (!$tab->save()) {
        return false;
    }

    return true;
}
