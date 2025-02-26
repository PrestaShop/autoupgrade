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
