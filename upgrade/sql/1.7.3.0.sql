SET SESSION sql_mode = '';
SET NAMES 'utf8';

UPDATE `PREFIX_tab` SET `position` = 0 WHERE `class_name` = 'AdminZones' AND `position` = '1';
UPDATE `PREFIX_tab` SET `position` = 1 WHERE `class_name` = 'AdminCountries' AND `position` = '0';

/* PHP:ps_1730_add_quick_access_evaluation_catalog(); */;

/* PHP:ps_1730_move_some_aeuc_configuration_to_core(); */;

/* PHP:add_column('product', 'low_stock_threshold', 'INT(10) NULL DEFAULT NULL AFTER `minimal_quantity`'); */;
/* PHP:add_column('product', 'additional_delivery_times', 'tinyint(1) unsigned NOT NULL DEFAULT \'1\' AFTER `out_of_stock`'); */;
/* PHP:add_column('product_lang', 'delivery_in_stock', 'varchar(255) DEFAULT NULL'); */;
/* PHP:add_column('product_lang', 'delivery_out_stock', 'varchar(255) DEFAULT NULL'); */;
/* PHP:add_column('product_shop', 'low_stock_threshold', 'INT(10) NULL DEFAULT NULL AFTER `minimal_quantity`'); */;
/* PHP:add_column('product_attribute', 'low_stock_threshold', 'INT(10) NULL DEFAULT NULL AFTER `minimal_quantity`'); */;
/* PHP:add_column('product_attribute_shop', 'low_stock_threshold', 'INT(10) NULL DEFAULT NULL AFTER `minimal_quantity`'); */;
/* PHP:add_column('product', 'low_stock_alert', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `low_stock_threshold`'); */;
/* PHP:add_column('product_shop', 'low_stock_alert', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `low_stock_threshold`'); */;
/* PHP:add_column('product_attribute', 'low_stock_alert', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `low_stock_threshold`'); */;
/* PHP:add_column('product_attribute_shop', 'low_stock_alert', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `low_stock_threshold`'); */;

CREATE TABLE IF NOT EXISTS `PREFIX_store_lang` (
  `id_store` int(11) unsigned NOT NULL,
  `id_lang` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `address1` varchar(255) NOT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `hours` text,
  `note` text,
  PRIMARY KEY (`id_store`, `id_lang`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

/* PHP:ps_1730_migrate_data_from_store_to_store_lang_and_clean_store(); */;
/* PHP:drop_column_if_exists('store', 'name'); */;
/* PHP:drop_column_if_exists('store', 'address1'); */;
/* PHP:drop_column_if_exists('store', 'address2'); */;
/* PHP:drop_column_if_exists('store', 'hours'); */;
/* PHP:drop_column_if_exists('store', 'note'); */;

ALTER TABLE `PREFIX_feature_product` DROP PRIMARY KEY, ADD PRIMARY KEY (`id_feature`, `id_product`, `id_feature_value`);

/* PHP:add_column('customization_field', 'is_deleted', 'TINYINT(1) NOT NULL DEFAULT \'0\''); */;

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'displayAdminCustomersAddressesItemAction', 'Display new elements in the Back Office, tab AdminCustomers, Addresses actions', 'This hook launches modules when the Addresses list into the AdminCustomers tab is displayed in the Back Office', '1'),
  (NULL, 'displayDashboardToolbarTopMenu', 'Display new elements in back office page with a dashboard, on top Menu', 'This hook launches modules when a page with a dashboard is displayed', '1'),
  (NULL, 'displayDashboardToolbarIcons', 'Display new elements in back office page with dashboard, on icons list', 'This hook launches modules when the back office with dashboard is displayed', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

INSERT IGNORE INTO `PREFIX_authorization_role` (`slug`) VALUES
  ('ROLE_MOD_TAB_DEFAULT_CREATE'),
  ('ROLE_MOD_TAB_DEFAULT_READ'),
  ('ROLE_MOD_TAB_DEFAULT_UPDATE'),
  ('ROLE_MOD_TAB_DEFAULT_DELETE');

UPDATE `PREFIX_configuration` SET `value` = 'In Stock' WHERE `name` = "PS_LABEL_IN_STOCK_PRODUCTS" AND `value` IS NULL;
UPDATE `PREFIX_configuration` SET `value` = 'Product available for orders' WHERE `name` = "PS_LABEL_OOS_PRODUCTS_BOA" AND `value` IS NULL;
UPDATE `PREFIX_configuration` SET `value` = 'Out-of-Stock' WHERE `name` = "PS_LABEL_OOS_PRODUCTS_BOD" AND `value` IS NULL;
UPDATE `PREFIX_configuration` SET `value` = '28' WHERE `name` = "SHOP_LOGO_HEIGHT" AND `value` = '23';
UPDATE `PREFIX_configuration` SET `value` = '100' WHERE `name` = "SHOP_LOGO_WIDTH" AND `value` = '117';
