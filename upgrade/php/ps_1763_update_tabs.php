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
function ps_1763_update_tabs()
{
    include_once 'add_new_tab.php';
    include_once 'copy_tab_rights.php';

    add_new_tab_17('AdminParentMailTheme', 'en:Email Themes', 0, false, 'AdminParentThemes');
    DbWrapper::execute(
        'UPDATE `' . _DB_PREFIX_ . 'tab` SET `active`= 1, `position`= 2 WHERE `class_name` = "AdminParentMailTheme"'
    );

    // Move AdminMailTheme's parent from AdminMailThemeParent to AdminParentMailTheme
    $toParentTabId = DbWrapper::getValue(
        'SELECT id_tab FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` = "AdminParentMailTheme"'
    );
    DbWrapper::execute(
        'UPDATE `' . _DB_PREFIX_ . 'tab` SET `id_parent` = ' . $toParentTabId . ' WHERE class_name = "AdminMailTheme"'
    );

    copy_tab_rights('AdminMailTheme', 'AdminParentMailTheme');
    DbWrapper::execute(
        'DELETE FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` = "AdminMailThemeParent"'
    );
}
