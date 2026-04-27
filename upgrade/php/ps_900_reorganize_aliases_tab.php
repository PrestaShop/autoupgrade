<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

 use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * @return void
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_900_reorganize_aliases_tab()
{
    include_once __DIR__ . '/add_new_tab.php';

    // Add new tab for aliases
    add_new_tab_17('AdminAliases', 'en:Aliases', 0, false, 'AdminParentSearchConf');
    DbWrapper::execute('UPDATE `' . _DB_PREFIX_ . 'tab` SET `active`=1, `enabled`=1, `wording`="Aliases", `icon`="", `wording_domain`="Admin.Navigation.Menu" WHERE `class_name` = "AdminAliases"');
}
