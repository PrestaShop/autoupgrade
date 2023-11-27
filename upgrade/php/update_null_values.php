<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

/**
 * This function aims to update the null values of several columns, to default values,
 * in order to avoid unexpected behavior on data that does not take null values into account
 *
 * @internal
 */
function update_null_values()
{
    $db = Db::getInstance();

    $updates = [
        ['address', 'address2', ''],
        ['address', 'company', ''],
        ['address', 'dni', ''],
        ['address', 'id_state', '0'],
        ['address', 'other', ''],
        ['address', 'phone_mobile', ''],
        ['address', 'phone', ''],
        ['address', 'postcode', ''],
        ['address', 'vat_number', ''],
        ['attachment_lang', 'description', ''],
        ['carrier_lang', 'delay', ''],
        ['carrier', 'external_module_name', ''],
        ['cart_rule', 'description', ''],
        ['cart', 'gift_message', ''],
        ['category_lang', 'additional_description', ''],
        ['category_lang', 'description', ''],
        ['category_lang', 'meta_description', ''],
        ['category_lang', 'meta_keywords', ''],
        ['category_lang', 'meta_title', ''],
        ['cms_category_lang', 'description', ''],
        ['cms_category_lang', 'meta_description', ''],
        ['cms_category_lang', 'meta_keywords', ''],
        ['cms_category_lang', 'meta_title', ''],
        ['cms_lang', 'content', ''],
        ['cms_lang', 'head_seo_title', ''],
        ['cms_lang', 'meta_description', ''],
        ['cms_lang', 'meta_keywords', ''],
        ['configuration_kpi_lang', 'date_upd', 'CURRENT_TIMESTAMP'],
        ['configuration_kpi_lang', 'value', ''],
        ['configuration_lang', 'date_upd', 'CURRENT_TIMESTAMP'],
        ['configuration_lang', 'value', ''],
        ['connections_source', 'http_referer', ''],
        ['connections_source', 'keywords', ''],
        ['connections', 'http_referer', ''],
        ['connections', 'ip_address', '0'],
        ['contact_lang', 'description', ''],
        ['currency_lang', 'pattern', ''],
        ['customer_message', 'file_name', ''],
        ['customer_message', 'id_employee', '0'],
        ['customer_message', 'ip_address', ''],
        ['customer_message', 'user_agent', ''],
        ['customer_thread', 'id_order', '0'],
        ['customer_thread', 'id_product', '0'],
        ['customer', 'birthday', '0000-00-00'],
        ['customer', 'newsletter_date_add', '0000-00-00 00:00:00'],
        ['customer', 'reset_password_validity', '0000-00-00 00:00:00'],
        ['employee', 'bo_css', ''],
        ['employee', 'reset_password_validity', '0000-00-00 00:00:00'],
        ['employee', 'stats_compare_from', '0000-00-00'],
        ['employee', 'stats_compare_to', '0000-00-00'],
        ['employee', 'stats_date_from', 'CURRENT_TIMESTAMP'],
        ['employee', 'stats_date_to', 'CURRENT_TIMESTAMP'],
        ['feature_value', 'custom', '0'],
        ['guest', 'accept_language', ''],
        ['guest', 'adobe_director', '0'],
        ['guest', 'adobe_flash', '0'],
        ['guest', 'apple_quicktime', '0'],
        ['guest', 'id_customer', '0'],
        ['guest', 'id_operating_system', '0'],
        ['guest', 'id_web_browser', '0'],
        ['guest', 'real_player', '0'],
        ['guest', 'screen_color', '0'],
        ['guest', 'screen_resolution_x', '0'],
        ['guest', 'screen_resolution_y', '0'],
        ['guest', 'sun_java', '0'],
        ['guest', 'windows_media', '0'],
        ['hook', 'description', ''],
        ['image_lang', 'legend', ''],
        ['log', 'error_code', '0'],
        ['log', 'id_employee', '0'],
        ['log', 'id_lang', '0'],
        ['log', 'object_id', '0'],
        ['log', 'object_type', ''],
        ['manufacturer_lang', 'description', ''],
        ['manufacturer_lang', 'meta_description', ''],
        ['manufacturer_lang', 'meta_keywords', ''],
        ['manufacturer_lang', 'meta_title', ''],
        ['manufacturer_lang', 'short_description', ''],
        ['message', 'id_employee', '0'],
        ['meta_lang', 'description', ''],
        ['meta_lang', 'keywords', ''],
        ['meta_lang', 'title', ''],
        ['order_carrier', 'id_order_invoice', '0'],
        ['order_carrier', 'shipping_cost_tax_excl', '0'],
        ['order_carrier', 'shipping_cost_tax_incl', '0'],
        ['order_carrier', 'tracking_number', ''],
        ['order_carrier', 'weight', '0'],
        ['order_detail', 'download_deadline', '0000-00-00 00:00:00'],
        ['order_detail', 'download_hash', ''],
        ['order_detail', 'id_order_invoice', '0'],
        ['order_detail', 'product_attribute_id', '0'],
        ['order_detail', 'product_ean13', ''],
        ['order_detail', 'product_isbn', ''],
        ['order_detail', 'product_mpn', ''],
        ['order_detail', 'product_reference', ''],
        ['order_detail', 'product_supplier_reference', ''],
        ['order_detail', 'product_upc', ''],
        ['order_invoice', 'delivery_date', '0000-00-00 00:00:00'],
        ['order_invoice', 'note', ''],
        ['order_invoice', 'shop_address', ''],
        ['order_payment', 'card_brand', ''],
        ['order_payment', 'card_expiration', ''],
        ['order_payment', 'card_holder', ''],
        ['order_payment', 'card_number', ''],
        ['order_payment', 'transaction_id', ''],
        ['order_return_state', 'color', ''],
        ['order_slip_detail', 'amount_tax_excl', '0'],
        ['order_slip_detail', 'amount_tax_incl', '0'],
        ['order_slip_detail', 'total_price_tax_excl', '0'],
        ['order_slip_detail', 'total_price_tax_incl', '0'],
        ['order_slip_detail', 'unit_price_tax_excl', '0'],
        ['order_slip_detail', 'unit_price_tax_incl', '0'],
        ['order_slip', 'total_products_tax_excl', '0'],
        ['order_slip', 'total_products_tax_incl', '0'],
        ['order_slip', 'total_shipping_tax_excl', '0'],
        ['order_slip', 'total_shipping_tax_incl', '0'],
        ['order_state', 'color', ''],
        ['order_state', 'module_name', ''],
        ['orders', 'gift_message', ''],
        ['orders', 'note', ''],
        ['product_attribute_lang', 'available_later', ''],
        ['product_attribute_lang', 'available_now', ''],
        ['product_attribute_shop', 'available_date', '0000-00-00'],
        ['product_attribute_shop', 'low_stock_threshold', '0'],
        ['product_attribute', 'available_date', '0000-00-00'],
        ['product_attribute', 'ean13', ''],
        ['product_attribute', 'isbn', ''],
        ['product_attribute', 'low_stock_threshold', '0'],
        ['product_attribute', 'mpn', ''],
        ['product_attribute', 'reference', ''],
        ['product_attribute', 'supplier_reference', ''],
        ['product_attribute', 'upc', ''],
        ['product_download', 'date_expiration', '0000-00-00 00:00:00'],
        ['product_download', 'nb_days_accessible', '0'],
        ['product_lang', 'available_later', ''],
        ['product_lang', 'available_now', ''],
        ['product_lang', 'delivery_in_stock', ''],
        ['product_lang', 'delivery_out_stock', ''],
        ['product_lang', 'description_short', ''],
        ['product_lang', 'description', ''],
        ['product_lang', 'meta_description', ''],
        ['product_lang', 'meta_keywords', ''],
        ['product_lang', 'meta_title', ''],
        ['product_sale', 'date_upd', 'CURRENT_TIMESTAMP'],
        ['product_shop', 'available_date', '0000-00-00'],
        ['product_shop', 'cache_default_attribute', '0'],
        ['product_shop', 'id_category_default', '0'],
        ['product_shop', 'low_stock_threshold', '0'],
        ['product_shop', 'unity', ''],
        ['product_supplier', 'product_supplier_reference', ''],
        ['product', 'available_date', '0000-00-00'],
        ['product', 'cache_default_attribute', '0'],
        ['product', 'ean13', ''],
        ['product', 'id_category_default', '0'],
        ['product', 'id_manufacturer', '0'],
        ['product', 'id_supplier', '0'],
        ['product', 'isbn', ''],
        ['product', 'low_stock_threshold', '0'],
        ['product', 'mpn', ''],
        ['product', 'reference', ''],
        ['product', 'supplier_reference', ''],
        ['product', 'unity', ''],
        ['product', 'upc', ''],
        ['risk', 'color', ''],
        ['stock', 'ean13', ''],
        ['stock', 'isbn', ''],
        ['stock', 'mpn', ''],
        ['stock', 'upc', ''],
        ['store_lang', 'address2', ''],
        ['store_lang', 'note', ''],
        ['store', 'email', ''],
        ['store', 'fax', ''],
        ['store', 'id_state', '0'],
        ['store', 'phone', ''],
        ['supplier_lang', 'description', ''],
        ['supplier_lang', 'meta_description', ''],
        ['supplier_lang', 'meta_keywords', ''],
        ['supplier_lang', 'meta_title', ''],
        ['supply_order_detail', 'ean13', ''],
        ['supply_order_detail', 'isbn', ''],
        ['supply_order_detail', 'mpn', ''],
        ['supply_order_detail', 'upc', ''],
        ['supply_order_state', 'color', ''],
        ['supply_order', 'date_delivery_expected', '0000-00-00 00:00:00'],
        ['warehouse_product_location', 'location', ''],
        ['warehouse', 'reference', ''],
        ['webservice_account', 'description', ''],
    ];

    foreach ($updates as $update) {
        // Extract values from the update array
        list($tabName, $columnName, $newValue) = $update;

        // Check if the table exists
        if (empty($db->executeS('SHOW TABLES LIKE "' . _DB_PREFIX_ . $tabName . '"'))) {
            continue;
        }

        // Check if the column exists
        if (empty($db->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . $tabName . "` WHERE Field = '" . $columnName . "'"))) {
            continue;
        }

        $newValue = $newValue === 'CURRENT_TIMESTAMP' ? 'CURRENT_TIMESTAMP' : '\'' . $newValue . '\'';

        // Update existing null values
        $updateQuery = 'UPDATE `' . _DB_PREFIX_ . $tabName . '` SET `' . $columnName . '`=' . $newValue . ' WHERE `' . $columnName . '` IS NULL';
        $db->execute($updateQuery);
    }
}
