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
function add_new_status_stock()
{
    Language::resetCache();
    $translator = Context::getContext()->getTranslator();
    $languages = Language::getLanguages();

    // insert ps_tab AdminStockManagement
    $count = (int) DbWrapper::getValue(
        'SELECT count(id_tab) FROM `' . _DB_PREFIX_ . 'tab` 
        WHERE `class_name` = \'AdminStockManagement\'
        AND `id_parent` = 9'
    );
    if (!$count) {
        DbWrapper::execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'tab`
            (`id_tab`, `id_parent`, `position`, `module`, `class_name`, `active`, `hide_host_mode`, `icon`)
            VALUES (null, 9, 7, NULL, \'AdminStockManagement\', 1, 0, \'\')'
        );
        $lastIdTab = (int) DbWrapper::Insert_ID();

        // ps_tab_lang
        foreach ($languages as $lang) {
            $idLang = (int) $lang['id_lang'];
            $stockName = pSQL(
                $translator->trans(
                    'Stock',
                    [],
                    'Admin.Navigation.Menu',
                    $lang['locale']
                )
            );
            DbWrapper::execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'tab_lang` (`id_tab`, `id_lang`, `name`) 
                VALUES (
                  ' . $lastIdTab . ', 
                  ' . $idLang . ", 
                  '" . $stockName . "'
                )"
            );
        }
    }

    // Stock movements
    $data = [
        [
            'name' => 'Customer Order',
            'sign' => 1,
            'conf' => 'PS_STOCK_CUSTOMER_ORDER_CANCEL_REASON',
        ],
        [
            'name' => 'Product Return',
            'sign' => 1,
            'conf' => 'PS_STOCK_CUSTOMER_RETURN_REASON',
        ],
        [
            'name' => 'Employee Edition',
            'sign' => 1,
            'conf' => 'PS_STOCK_MVT_INC_EMPLOYEE_EDITION',
        ],
        [
            'name' => 'Employee Edition',
            'sign' => -1,
            'conf' => 'PS_STOCK_MVT_DEC_EMPLOYEE_EDITION',
        ],
    ];

    foreach ($data as $d) {
        // We don't want duplicated data
        if (configuration_exists($d['conf'])) {
            continue;
        }

        // ps_stock_mvt_reason
        DbWrapper::execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'stock_mvt_reason` (`sign`, `date_add`, `date_upd`, `deleted`)
            VALUES (' . $d['sign'] . ', NOW(), NOW(), "0")'
        );

        // ps_configuration
        $lastInsertedId = DbWrapper::Insert_ID();
        DbWrapper::execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'configuration` (`name`, `value`, `date_add`, `date_upd`)
            VALUES ("' . $d['conf'] . '", ' . (int) $lastInsertedId . ', NOW(), NOW())'
        );

        // ps_stock_mvt_reason_lang
        foreach ($languages as $lang) {
            $mvtName = pSQL(
                $translator->trans(
                    $d['name'],
                    [],
                    'Admin.Catalog.Feature',
                    $lang['locale']
                )
            );
            DbWrapper::execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'stock_mvt_reason_lang` (`id_stock_mvt_reason`, `id_lang`, `name`)
                VALUES (' . (int) $lastInsertedId . ', ' . (int) $lang['id_lang'] . ', "' . $mvtName . '")'
            );
        }
    }

    // sync all stock
    $shops = Shop::getShops();
    foreach ($shops as $shop) {
        (new \PrestaShop\PrestaShop\Adapter\StockManager())->updatePhysicalProductQuantity(
            (int) $shop['id_shop'],
            (int) Configuration::get('PS_OS_ERROR'),
            (int) Configuration::get('PS_OS_CANCELED')
        );
    }
}

/**
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function configuration_exists($confName)
{
    $count = (int) DbWrapper::getValue(
        'SELECT count(id_configuration)
        FROM `' . _DB_PREFIX_ . 'configuration` 
        WHERE `name` = \'' . $confName . '\''
    );

    return $count > 0;
}
