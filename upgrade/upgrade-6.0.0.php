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
