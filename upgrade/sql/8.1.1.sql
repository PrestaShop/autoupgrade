SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/* Normalize some older database records that should not be NULL, prevents errors in new code. */
/* Mainly concerns people coming all the way from 1.6, but will fix many inconsitencies. */
UPDATE `PREFIX_address` SET `address2`='' WHERE `address2` IS NULL;
UPDATE `PREFIX_address` SET `company`='' WHERE `company` IS NULL;
UPDATE `PREFIX_address` SET `dni`='' WHERE `dni` IS NULL;
UPDATE `PREFIX_address` SET `id_state`='0' WHERE `id_state` IS NULL;
UPDATE `PREFIX_address` SET `other`='' WHERE `other` IS NULL;
UPDATE `PREFIX_address` SET `phone_mobile`='' WHERE `phone_mobile` IS NULL;
UPDATE `PREFIX_address` SET `phone`='' WHERE `phone` IS NULL;
UPDATE `PREFIX_address` SET `postcode`='' WHERE `postcode` IS NULL;
UPDATE `PREFIX_address` SET `vat_number`='' WHERE `vat_number` IS NULL;
UPDATE `PREFIX_attachment_lang` SET `description`='' WHERE `description` IS NULL;
UPDATE `PREFIX_carrier_lang` SET `delay`='' WHERE `delay` IS NULL;
UPDATE `PREFIX_carrier` SET `external_module_name`='' WHERE `external_module_name` IS NULL;
UPDATE `PREFIX_carrier` SET `url`='' WHERE `url` IS NULL;
UPDATE `PREFIX_cart_rule` SET `description`='' WHERE `description` IS NULL;
UPDATE `PREFIX_cart` SET `gift_message`='' WHERE `gift_message` IS NULL;
UPDATE `PREFIX_category_lang` SET `additional_description`='' WHERE `additional_description` IS NULL;
UPDATE `PREFIX_category_lang` SET `description`='' WHERE `description` IS NULL;
UPDATE `PREFIX_category_lang` SET `meta_description`='' WHERE `meta_description` IS NULL;
UPDATE `PREFIX_category_lang` SET `meta_keywords`='' WHERE `meta_keywords` IS NULL;
UPDATE `PREFIX_category_lang` SET `meta_title`='' WHERE `meta_title` IS NULL;
UPDATE `PREFIX_cms_category_lang` SET `description`='' WHERE `description` IS NULL;
UPDATE `PREFIX_cms_category_lang` SET `meta_description`='' WHERE `meta_description` IS NULL;
UPDATE `PREFIX_cms_category_lang` SET `meta_keywords`='' WHERE `meta_keywords` IS NULL;
UPDATE `PREFIX_cms_category_lang` SET `meta_title`='' WHERE `meta_title` IS NULL;
UPDATE `PREFIX_cms_lang` SET `content`='' WHERE `content` IS NULL;
UPDATE `PREFIX_cms_lang` SET `head_seo_title`='' WHERE `head_seo_title` IS NULL;
UPDATE `PREFIX_cms_lang` SET `meta_description`='' WHERE `meta_description` IS NULL;
UPDATE `PREFIX_cms_lang` SET `meta_keywords`='' WHERE `meta_keywords` IS NULL;
UPDATE `PREFIX_configuration_kpi_lang` SET `date_upd`=CURRENT_TIMESTAMP WHERE `date_upd` IS NULL;
UPDATE `PREFIX_configuration_kpi_lang` SET `value`='' WHERE `value` IS NULL;
UPDATE `PREFIX_configuration_lang` SET `date_upd`=CURRENT_TIMESTAMP WHERE `date_upd` IS NULL;
UPDATE `PREFIX_configuration_lang` SET `value`='' WHERE `value` IS NULL;
UPDATE `PREFIX_connections_source` SET `http_referer`='' WHERE `http_referer` IS NULL;
UPDATE `PREFIX_connections_source` SET `keywords`='' WHERE `keywords` IS NULL;
UPDATE `PREFIX_connections` SET `http_referer`='' WHERE `http_referer` IS NULL;
UPDATE `PREFIX_connections` SET `ip_address`='0' WHERE `ip_address` IS NULL;
UPDATE `PREFIX_contact_lang` SET `description`='' WHERE `description` IS NULL;
UPDATE `PREFIX_currency_lang` SET `pattern`='' WHERE `pattern` IS NULL;
UPDATE `PREFIX_customer_message` SET `file_name`='' WHERE `file_name` IS NULL;
UPDATE `PREFIX_customer_message` SET `id_employee`='0' WHERE `id_employee` IS NULL;
UPDATE `PREFIX_customer_message` SET `ip_address`='' WHERE `ip_address` IS NULL;
UPDATE `PREFIX_customer_message` SET `user_agent`='' WHERE `user_agent` IS NULL;
UPDATE `PREFIX_customer_thread` SET `id_order`='0' WHERE `id_order` IS NULL;
UPDATE `PREFIX_customer_thread` SET `id_product`='0' WHERE `id_product` IS NULL;
UPDATE `PREFIX_customer` SET `birthday`='0000-00-00' WHERE `birthday` IS NULL;
UPDATE `PREFIX_customer` SET `newsletter_date_add`='0000-00-00 00:00:00' WHERE `newsletter_date_add` IS NULL;
UPDATE `PREFIX_customer` SET `reset_password_validity`='0000-00-00 00:00:00' WHERE `reset_password_validity` IS NULL;
UPDATE `PREFIX_employee` SET `bo_css`='' WHERE `bo_css` IS NULL;
UPDATE `PREFIX_employee` SET `reset_password_validity`='0000-00-00 00:00:00' WHERE `reset_password_validity` IS NULL;
UPDATE `PREFIX_employee` SET `stats_compare_from`='0000-00-00' WHERE `stats_compare_from` IS NULL;
UPDATE `PREFIX_employee` SET `stats_compare_to`='0000-00-00' WHERE `stats_compare_to` IS NULL;
UPDATE `PREFIX_employee` SET `stats_date_from`=CURRENT_TIMESTAMP WHERE `stats_date_from` IS NULL;
UPDATE `PREFIX_employee` SET `stats_date_to`=CURRENT_TIMESTAMP WHERE `stats_date_to` IS NULL;
UPDATE `PREFIX_feature_value` SET `custom`='0' WHERE `custom` IS NULL;
UPDATE `PREFIX_guest` SET `accept_language`='' WHERE `accept_language` IS NULL;
UPDATE `PREFIX_guest` SET `adobe_director`='0' WHERE `adobe_director` IS NULL;
UPDATE `PREFIX_guest` SET `adobe_flash`='0' WHERE `adobe_flash` IS NULL;
UPDATE `PREFIX_guest` SET `apple_quicktime`='0' WHERE `apple_quicktime` IS NULL;
UPDATE `PREFIX_guest` SET `id_customer`='0' WHERE `id_customer` IS NULL;
UPDATE `PREFIX_guest` SET `id_operating_system`='0' WHERE `id_operating_system` IS NULL;
UPDATE `PREFIX_guest` SET `id_web_browser`='0' WHERE `id_web_browser` IS NULL;
UPDATE `PREFIX_guest` SET `real_player`='0' WHERE `real_player` IS NULL;
UPDATE `PREFIX_guest` SET `screen_color`='0' WHERE `screen_color` IS NULL;
UPDATE `PREFIX_guest` SET `screen_resolution_x`='0' WHERE `screen_resolution_x` IS NULL;
UPDATE `PREFIX_guest` SET `screen_resolution_y`='0' WHERE `screen_resolution_y` IS NULL;
UPDATE `PREFIX_guest` SET `sun_java`='0' WHERE `sun_java` IS NULL;
UPDATE `PREFIX_guest` SET `windows_media`='0' WHERE `windows_media` IS NULL;
UPDATE `PREFIX_hook` SET `description`='' WHERE `description` IS NULL;
UPDATE `PREFIX_image_lang` SET `legend`='' WHERE `legend` IS NULL;
UPDATE `PREFIX_log` SET `error_code`='0' WHERE `error_code` IS NULL;
UPDATE `PREFIX_log` SET `id_employee`='0' WHERE `id_employee` IS NULL;
UPDATE `PREFIX_log` SET `id_lang`='0' WHERE `id_lang` IS NULL;
UPDATE `PREFIX_log` SET `object_id`='0' WHERE `object_id` IS NULL;
UPDATE `PREFIX_log` SET `object_type`='' WHERE `object_type` IS NULL;
UPDATE `PREFIX_manufacturer_lang` SET `description`='' WHERE `description` IS NULL;
UPDATE `PREFIX_manufacturer_lang` SET `meta_description`='' WHERE `meta_description` IS NULL;
UPDATE `PREFIX_manufacturer_lang` SET `meta_keywords`='' WHERE `meta_keywords` IS NULL;
UPDATE `PREFIX_manufacturer_lang` SET `meta_title`='' WHERE `meta_title` IS NULL;
UPDATE `PREFIX_manufacturer_lang` SET `short_description`='' WHERE `short_description` IS NULL;
UPDATE `PREFIX_message` SET `id_employee`='0' WHERE `id_employee` IS NULL;
UPDATE `PREFIX_meta_lang` SET `description`='' WHERE `description` IS NULL;
UPDATE `PREFIX_meta_lang` SET `keywords`='' WHERE `keywords` IS NULL;
UPDATE `PREFIX_meta_lang` SET `title`='' WHERE `title` IS NULL;
UPDATE `PREFIX_order_carrier` SET `id_order_invoice`='0' WHERE `id_order_invoice` IS NULL;
UPDATE `PREFIX_order_carrier` SET `shipping_cost_tax_excl`='0' WHERE `shipping_cost_tax_excl` IS NULL;
UPDATE `PREFIX_order_carrier` SET `shipping_cost_tax_incl`='0' WHERE `shipping_cost_tax_incl` IS NULL;
UPDATE `PREFIX_order_carrier` SET `tracking_number`='' WHERE `tracking_number` IS NULL;
UPDATE `PREFIX_order_carrier` SET `weight`='0' WHERE `weight` IS NULL;
UPDATE `PREFIX_order_detail` SET `download_deadline`='0000-00-00 00:00:00' WHERE `download_deadline` IS NULL;
UPDATE `PREFIX_order_detail` SET `download_hash`='' WHERE `download_hash` IS NULL;
UPDATE `PREFIX_order_detail` SET `id_order_invoice`='0' WHERE `id_order_invoice` IS NULL;
UPDATE `PREFIX_order_detail` SET `product_attribute_id`='0' WHERE `product_attribute_id` IS NULL;
UPDATE `PREFIX_order_detail` SET `product_ean13`='' WHERE `product_ean13` IS NULL;
UPDATE `PREFIX_order_detail` SET `product_isbn`='' WHERE `product_isbn` IS NULL;
UPDATE `PREFIX_order_detail` SET `product_mpn`='' WHERE `product_mpn` IS NULL;
UPDATE `PREFIX_order_detail` SET `product_reference`='' WHERE `product_reference` IS NULL;
UPDATE `PREFIX_order_detail` SET `product_supplier_reference`='' WHERE `product_supplier_reference` IS NULL;
UPDATE `PREFIX_order_detail` SET `product_upc`='' WHERE `product_upc` IS NULL;
UPDATE `PREFIX_order_invoice` SET `delivery_date`='0000-00-00 00:00:00' WHERE `delivery_date` IS NULL;
UPDATE `PREFIX_order_invoice` SET `note`='' WHERE `note` IS NULL;
UPDATE `PREFIX_order_invoice` SET `shop_address`='' WHERE `shop_address` IS NULL;
UPDATE `PREFIX_order_payment` SET `card_brand`='' WHERE `card_brand` IS NULL;
UPDATE `PREFIX_order_payment` SET `card_expiration`='' WHERE `card_expiration` IS NULL;
UPDATE `PREFIX_order_payment` SET `card_holder`='' WHERE `card_holder` IS NULL;
UPDATE `PREFIX_order_payment` SET `card_number`='' WHERE `card_number` IS NULL;
UPDATE `PREFIX_order_payment` SET `transaction_id`='' WHERE `transaction_id` IS NULL;
UPDATE `PREFIX_order_return_state` SET `color`='' WHERE `color` IS NULL;
UPDATE `PREFIX_order_slip_detail` SET `amount_tax_excl`='0' WHERE `amount_tax_excl` IS NULL;
UPDATE `PREFIX_order_slip_detail` SET `amount_tax_incl`='0' WHERE `amount_tax_incl` IS NULL;
UPDATE `PREFIX_order_slip_detail` SET `total_price_tax_excl`='0' WHERE `total_price_tax_excl` IS NULL;
UPDATE `PREFIX_order_slip_detail` SET `total_price_tax_incl`='0' WHERE `total_price_tax_incl` IS NULL;
UPDATE `PREFIX_order_slip_detail` SET `unit_price_tax_excl`='0' WHERE `unit_price_tax_excl` IS NULL;
UPDATE `PREFIX_order_slip_detail` SET `unit_price_tax_incl`='0' WHERE `unit_price_tax_incl` IS NULL;
UPDATE `PREFIX_order_slip` SET `total_products_tax_excl`='0' WHERE `total_products_tax_excl` IS NULL;
UPDATE `PREFIX_order_slip` SET `total_products_tax_incl`='0' WHERE `total_products_tax_incl` IS NULL;
UPDATE `PREFIX_order_slip` SET `total_shipping_tax_excl`='0' WHERE `total_shipping_tax_excl` IS NULL;
UPDATE `PREFIX_order_slip` SET `total_shipping_tax_incl`='0' WHERE `total_shipping_tax_incl` IS NULL;
UPDATE `PREFIX_order_state` SET `color`='' WHERE `color` IS NULL;
UPDATE `PREFIX_order_state` SET `module_name`='' WHERE `module_name` IS NULL;
UPDATE `PREFIX_orders` SET `gift_message`='' WHERE `gift_message` IS NULL;
UPDATE `PREFIX_orders` SET `note`='' WHERE `note` IS NULL;
UPDATE `PREFIX_product_attribute_lang` SET `available_later`='' WHERE `available_later` IS NULL;
UPDATE `PREFIX_product_attribute_lang` SET `available_now`='' WHERE `available_now` IS NULL;
UPDATE `PREFIX_product_attribute_shop` SET `available_date`='0000-00-00' WHERE `available_date` IS NULL;
UPDATE `PREFIX_product_attribute_shop` SET `low_stock_threshold`='0' WHERE `low_stock_threshold` IS NULL;
UPDATE `PREFIX_product_attribute` SET `available_date`='0000-00-00' WHERE `available_date` IS NULL;
UPDATE `PREFIX_product_attribute` SET `ean13`='' WHERE `ean13` IS NULL;
UPDATE `PREFIX_product_attribute` SET `isbn`='' WHERE `isbn` IS NULL;
UPDATE `PREFIX_product_attribute` SET `low_stock_threshold`='0' WHERE `low_stock_threshold` IS NULL;
UPDATE `PREFIX_product_attribute` SET `mpn`='' WHERE `mpn` IS NULL;
UPDATE `PREFIX_product_attribute` SET `reference`='' WHERE `reference` IS NULL;
UPDATE `PREFIX_product_attribute` SET `supplier_reference`='' WHERE `supplier_reference` IS NULL;
UPDATE `PREFIX_product_attribute` SET `upc`='' WHERE `upc` IS NULL;
UPDATE `PREFIX_product_download` SET `date_expiration`='0000-00-00 00:00:00' WHERE `date_expiration` IS NULL;
UPDATE `PREFIX_product_download` SET `nb_days_accessible`='0' WHERE `nb_days_accessible` IS NULL;
UPDATE `PREFIX_product_lang` SET `available_later`='' WHERE `available_later` IS NULL;
UPDATE `PREFIX_product_lang` SET `available_now`='' WHERE `available_now` IS NULL;
UPDATE `PREFIX_product_lang` SET `delivery_in_stock`='' WHERE `delivery_in_stock` IS NULL;
UPDATE `PREFIX_product_lang` SET `delivery_out_stock`='' WHERE `delivery_out_stock` IS NULL;
UPDATE `PREFIX_product_lang` SET `description_short`='' WHERE `description_short` IS NULL;
UPDATE `PREFIX_product_lang` SET `description`='' WHERE `description` IS NULL;
UPDATE `PREFIX_product_lang` SET `meta_description`='' WHERE `meta_description` IS NULL;
UPDATE `PREFIX_product_lang` SET `meta_keywords`='' WHERE `meta_keywords` IS NULL;
UPDATE `PREFIX_product_lang` SET `meta_title`='' WHERE `meta_title` IS NULL;
UPDATE `PREFIX_product_sale` SET `date_upd`=CURRENT_TIMESTAMP WHERE `date_upd` IS NULL;
UPDATE `PREFIX_product_shop` SET `available_date`='0000-00-00' WHERE `available_date` IS NULL;
UPDATE `PREFIX_product_shop` SET `cache_default_attribute`='0' WHERE `cache_default_attribute` IS NULL;
UPDATE `PREFIX_product_shop` SET `id_category_default`='0' WHERE `id_category_default` IS NULL;
UPDATE `PREFIX_product_shop` SET `low_stock_threshold`='0' WHERE `low_stock_threshold` IS NULL;
UPDATE `PREFIX_product_shop` SET `unity`='' WHERE `unity` IS NULL;
UPDATE `PREFIX_product_supplier` SET `product_supplier_reference`='' WHERE `product_supplier_reference` IS NULL;
UPDATE `PREFIX_product` SET `available_date`='0000-00-00' WHERE `available_date` IS NULL;
UPDATE `PREFIX_product` SET `cache_default_attribute`='0' WHERE `cache_default_attribute` IS NULL;
UPDATE `PREFIX_product` SET `ean13`='' WHERE `ean13` IS NULL;
UPDATE `PREFIX_product` SET `id_category_default`='0' WHERE `id_category_default` IS NULL;
UPDATE `PREFIX_product` SET `id_manufacturer`='0' WHERE `id_manufacturer` IS NULL;
UPDATE `PREFIX_product` SET `id_supplier`='0' WHERE `id_supplier` IS NULL;
UPDATE `PREFIX_product` SET `isbn`='' WHERE `isbn` IS NULL;
UPDATE `PREFIX_product` SET `low_stock_threshold`='0' WHERE `low_stock_threshold` IS NULL;
UPDATE `PREFIX_product` SET `mpn`='' WHERE `mpn` IS NULL;
UPDATE `PREFIX_product` SET `reference`='' WHERE `reference` IS NULL;
UPDATE `PREFIX_product` SET `supplier_reference`='' WHERE `supplier_reference` IS NULL;
UPDATE `PREFIX_product` SET `unity`='' WHERE `unity` IS NULL;
UPDATE `PREFIX_product` SET `upc`='' WHERE `upc` IS NULL;
UPDATE `PREFIX_risk` SET `color`='' WHERE `color` IS NULL;
UPDATE `PREFIX_stock` SET `ean13`='' WHERE `ean13` IS NULL;
UPDATE `PREFIX_stock` SET `isbn`='' WHERE `isbn` IS NULL;
UPDATE `PREFIX_stock` SET `mpn`='' WHERE `mpn` IS NULL;
UPDATE `PREFIX_stock` SET `upc`='' WHERE `upc` IS NULL;
UPDATE `PREFIX_store_lang` SET `address2`='' WHERE `address2` IS NULL;
UPDATE `PREFIX_store_lang` SET `note`='' WHERE `note` IS NULL;
UPDATE `PREFIX_store` SET `email`='' WHERE `email` IS NULL;
UPDATE `PREFIX_store` SET `fax`='' WHERE `fax` IS NULL;
UPDATE `PREFIX_store` SET `id_state`='0' WHERE `id_state` IS NULL;
UPDATE `PREFIX_store` SET `phone`='' WHERE `phone` IS NULL;
UPDATE `PREFIX_supplier_lang` SET `description`='' WHERE `description` IS NULL;
UPDATE `PREFIX_supplier_lang` SET `meta_description`='' WHERE `meta_description` IS NULL;
UPDATE `PREFIX_supplier_lang` SET `meta_keywords`='' WHERE `meta_keywords` IS NULL;
UPDATE `PREFIX_supplier_lang` SET `meta_title`='' WHERE `meta_title` IS NULL;
UPDATE `PREFIX_supply_order_detail` SET `ean13`='' WHERE `ean13` IS NULL;
UPDATE `PREFIX_supply_order_detail` SET `isbn`='' WHERE `isbn` IS NULL;
UPDATE `PREFIX_supply_order_detail` SET `mpn`='' WHERE `mpn` IS NULL;
UPDATE `PREFIX_supply_order_detail` SET `upc`='' WHERE `upc` IS NULL;
UPDATE `PREFIX_supply_order_state` SET `color`='' WHERE `color` IS NULL;
UPDATE `PREFIX_supply_order` SET `date_delivery_expected`='0000-00-00 00:00:00' WHERE `date_delivery_expected` IS NULL;
UPDATE `PREFIX_warehouse_product_location` SET `location`='' WHERE `location` IS NULL;
UPDATE `PREFIX_warehouse` SET `reference`='' WHERE `reference` IS NULL;
UPDATE `PREFIX_webservice_account` SET `description`='' WHERE `description` IS NULL;
