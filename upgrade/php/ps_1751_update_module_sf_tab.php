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
