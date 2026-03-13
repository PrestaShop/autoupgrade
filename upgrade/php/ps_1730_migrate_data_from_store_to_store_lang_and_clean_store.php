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
function ps_1730_migrate_data_from_store_to_store_lang_and_clean_store()
{
    $langs = Language::getLanguages();
    foreach ($langs as $lang) {
        DbWrapper::execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'store_lang`
            SELECT `id_store`, ' . $lang['id_lang'] . ' as id_lang , `name`, `address1`, `address2`, `hours`, `note`
            FROM `' . _DB_PREFIX_ . 'store`'
        );
    }
}
