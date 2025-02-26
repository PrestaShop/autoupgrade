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
 * File copied from ps_update_tabs.php and modified for only adding modules related tabs
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_1740_update_module_tabs()
{
    // Add new sub menus for modules
    $moduleTabsToBeAdded = [
        'AdminModulesManage' => 'en:Installed modules|fr:Modules installés|es:Módulos instalados|de:Installierte Module|it:Moduli installati',
        'AdminModulesCatalog' => 'en:Selection|fr:Selection|es:Selección|de:Auswahl|it:Selezione',
        'AdminModulesNotifications' => 'en:Notifications|fr:Notifications|es:Notificaciones|de:Nachrichten|it:Notifiche',
    ];

    include_once 'add_new_tab.php';
    foreach ($moduleTabsToBeAdded as $className => $translations) {
        add_new_tab_17($className, $translations, 0, false, 'AdminModulesSf');
    }

    DbWrapper::execute('UPDATE `' . _DB_PREFIX_ . 'tab` SET `active`=1 WHERE `class_name` IN ("AdminModulesManage", "AdminModulesCatalog", "AdminModulesNotifications")');
}
