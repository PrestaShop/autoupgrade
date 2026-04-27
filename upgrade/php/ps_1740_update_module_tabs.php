<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
