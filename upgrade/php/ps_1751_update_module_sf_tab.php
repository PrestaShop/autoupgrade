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
function ps_1751_update_module_sf_tab()
{
    // Rename parent module tab (= Module manager)
    include_once 'rename_tab.php';
    $adminModulesParentTabId = DbWrapper::getValue(
        'SELECT id_tab FROM ' . _DB_PREFIX_ . 'tab WHERE class_name = "AdminModulesSf"'
    );
    if (!empty($adminModulesParentTabId)) {
        renameTab(
            $adminModulesParentTabId,
            [
                'fr' => 'Gestionnaire de modules',
                'es' => 'Gestor de módulos',
                'en' => 'Module Manager',
                'gb' => 'Module Manager',
                'de' => 'Modulmanager',
                'it' => 'Gestione moduli',
            ]
        );
    }
}
