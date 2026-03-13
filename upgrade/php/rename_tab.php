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
function renameTab($id_tab, $names)
{
    if (!$id_tab) {
        return;
    }
    $langues = DbWrapper::executeS('SELECT * FROM ' . _DB_PREFIX_ . 'lang');

    foreach ($langues as $lang) {
        if (array_key_exists($lang['iso_code'], $names)) {
            DbWrapper::update('tab_lang', ['name' => $names[$lang['iso_code']]], '`id_tab`= ' . $id_tab . ' AND `id_lang` =' . $lang['id_lang']);
        }
    }
}
