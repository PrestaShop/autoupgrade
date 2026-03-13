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
function ps_1750_update_module_tabs()
{
    // STEP 1: Add new sub menus for modules
    $moduleTabsToBeAdded = [
        'AdminModulesUpdates' => [
            'translations' => 'en:Updates|fr:Mises à jour|es:Actualizaciones|de:Aktualisierung|it:Aggiornamenti',
            'parent' => 'AdminModulesSf',
        ],
        'AdminParentModulesCatalog' => [
            'translations' => 'en:Module Catalog|fr:Catalogue de modules|es:Catálogo de módulos|de:Modulkatalog|it:Catalogo dei moduli',
            'parent' => 'AdminParentModulesSf',
        ],
    ];

    include_once 'add_new_tab.php';
    foreach ($moduleTabsToBeAdded as $className => $tabDetails) {
        add_new_tab_17($className, $tabDetails['translations'], 0, false, $tabDetails['parent']);
        DbWrapper::execute(
            'UPDATE `' . _DB_PREFIX_ . 'tab` SET `active`= 1 WHERE `class_name` = "' . $className . '"'
        );
    }

    // STEP 2: Rename module tabs (Notifications as Alerts, Module selection as Module Catalog, Module Catalog as Module Selections)
    include_once 'rename_tab.php';
    $adminModulesNotificationsTabId = DbWrapper::getValue(
        'SELECT id_tab FROM ' . _DB_PREFIX_ . 'tab WHERE class_name = "AdminModulesNotifications"'
    );
    if (!empty($adminModulesNotificationsTabId)) {
        renameTab(
            $adminModulesNotificationsTabId,
            [
                'fr' => 'Alertes',
                'es' => 'Alertas',
                'en' => 'Alerts',
                'gb' => 'Alerts',
                'de' => 'Benachrichtigungen',
                'it' => 'Avvisi',
            ]
        );
    }

    $adminModulesCatalogTabId = DbWrapper::getValue(
        'SELECT id_tab FROM ' . _DB_PREFIX_ . 'tab WHERE class_name = "AdminModulesCatalog"'
    );
    if (!empty($adminModulesCatalogTabId)) {
        renameTab(
            $adminModulesCatalogTabId,
            [
                'fr' => 'Catalogue de modules',
                'es' => 'Catálogo de módulos',
                'en' => 'Module Catalog',
                'gb' => 'Module Catalog',
                'de' => 'Versanddienst',
                'it' => 'Catalogo dei moduli',
            ]
        );
    }

    $adminModulesManageTabId = DbWrapper::getValue(
        'SELECT id_tab FROM ' . _DB_PREFIX_ . 'tab WHERE class_name = "AdminModulesManage"'
    );
    if (!empty($adminModulesManageTabId)) {
        renameTab(
            $adminModulesManageTabId,
            [
                'fr' => 'Modules',
                'es' => 'módulos',
                'en' => 'Modules',
                'gb' => 'Modules',
                'de' => 'Modules',
                'it' => 'Moduli',
            ]
        );
    }

    $adminModulesAddonsSelectionsTabId = DbWrapper::getValue(
        'SELECT id_tab FROM ' . _DB_PREFIX_ . 'tab WHERE class_name = "AdminAddonsCatalog"'
    );
    if (!empty($adminModulesAddonsSelectionsTabId)) {
        renameTab(
            $adminModulesAddonsSelectionsTabId,
            [
                'fr' => 'Sélections de modules',
                'es' => 'Selecciones de módulos',
                'en' => 'Module Selections',
                'gb' => 'Module Selections',
                'de' => 'Auswahl von Modulen',
                'it' => 'Selezioni Moduli',
            ]
        );
    }

    // STEP 3: Move the 2 module catalog controllers in the parent one
    // Get The ID of the parent
    $adminParentModuleCatalogTabId = DbWrapper::getValue(
        'SELECT id_tab FROM ' . _DB_PREFIX_ . 'tab WHERE class_name = "AdminParentModulesCatalog"'
    );
    foreach (['AdminModulesCatalog', 'AdminAddonsCatalog'] as $key => $className) {
        DbWrapper::execute(
            'UPDATE `' . _DB_PREFIX_ . 'tab` SET `id_parent`= ' . (int) $adminParentModuleCatalogTabId . ', position = ' . $key . ' WHERE `class_name` = "' . $className . '"'
        );
    }
}
