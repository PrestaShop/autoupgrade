<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * File copied from ps_1750_update_module_tabs.php and modified to add new roles
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_1760_update_tabs()
{
    // STEP 1: Add new sub menus for modules (tab may exist but we need authorization roles to be added as well)
    $moduleTabsToBeAdded = [
        'AdminMailThemeParent' => [
            'translations' => 'en:Email Themes',
            'parent' => 'AdminParentThemes',
        ],
        'AdminMailTheme' => [
            'translations' => 'en:Email Themes',
            'parent' => 'AdminMailThemeParent',
        ],
        'AdminModulesUpdates' => [
            'translations' => 'en:Updates|fr:Mises à jour|es:Actualizaciones|de:Aktualisierung|it:Aggiornamenti',
            'parent' => 'AdminModulesSf',
        ],
        'AdminModulesNotifications' => [
            'translations' => 'en:Updates|fr:Mises à jour|es:Actualizaciones|de:Aktualisierung|it:Aggiornamenti',
            'parent' => 'AdminModulesSf',
        ],
    ];

    include_once 'add_new_tab.php';
    foreach ($moduleTabsToBeAdded as $className => $tabDetails) {
        add_new_tab_17($className, $tabDetails['translations'], 0, false, $tabDetails['parent']);
        DbWrapper::execute(
            'UPDATE `' . _DB_PREFIX_ . 'tab` SET `active`= 1 WHERE `class_name` = "' . pSQL($className) . '"'
        );
    }
}
