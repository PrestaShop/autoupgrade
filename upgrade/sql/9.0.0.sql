SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/* Enable controlling of default language URL prefix - https://github.com/PrestaShop/PrestaShop/pull/37236 */
/* Add a file separator input to the sql manager settings - https://github.com/PrestaShop/PrestaShop/pull/35843 */
/* Allow configuring maximum word difference - https://github.com/PrestaShop/PrestaShop/pull/37261 */
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
  ('PS_DEBUG_COOKIE_NAME', '', NOW(), NOW()),
  ('PS_DEBUG_COOKIE_VALUE', '', NOW(), NOW()),
  ('PS_PRODUCT_BREADCRUMB_CATEGORY', 'default', NOW(), NOW()),
  ('PS_SEARCH_FUZZY_MAX_DIFFERENCE', 5, NOW(), NOW()),
  ('PS_DEFAULT_LANGUAGE_URL_PREFIX', 1, NOW(), NOW())
;

/* Remove meta keywords - https://github.com/PrestaShop/PrestaShop/pull/36873 */
/* PHP:drop_column_if_exists('category_lang', 'meta_keywords'); */;
/* PHP:drop_column_if_exists('cms_lang', 'meta_keywords'); */;
/* PHP:drop_column_if_exists('cms_category_lang', 'meta_keywords'); */;
/* PHP:drop_column_if_exists('manufacturer_lang', 'meta_keywords'); */;
/* PHP:drop_column_if_exists('meta_lang', 'keywords'); */;
/* PHP:drop_column_if_exists('product_lang', 'meta_keywords'); */;
/* PHP:drop_column_if_exists('supplier_lang', 'meta_keywords'); */;

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionUpdateCartAddress', 'Triggers after changing address on the cart', 'This hook is called after address is changed on the cart', '1'),
  (NULL, 'actionPresentCategory', 'Category Presenter', 'This hook is called before a category is presented', '1'),
  (NULL, 'actionPresentStore', 'Store Presenter', 'This hook is called before a store is presented', '1'),
  (NULL, 'actionPresentSupplier', 'Supplier Presenter', 'This hook is called before a supplier is presented', '1'),
  (NULL, 'actionPresentManufacturer', 'Manufacturer Presenter', 'This hook is called before a manufacturer is presented', '1'),
  (NULL, 'actionValidateCartRule', 'Alter cart rule validation process', 'Allow modules to implement their own rules to validate a cart rule.', '1'),
  (NULL, 'actionCartGetPackageShippingCost', 'After getting package shipping cost value', 'This hook is called in order to allow to modify package shipping cost', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

/* Add feature flag types */
ALTER TABLE `PREFIX_feature_flag` ADD `type` VARCHAR(64) DEFAULT 'env,dotenv,db' NOT NULL AFTER `name`;
UPDATE `PREFIX_feature_flag` SET `state` = 1 WHERE `name` = 'authorization_server';
UPDATE `PREFIX_tab` SET `active` = 1 WHERE `class_name` = 'AdminAuthorizationServer';

/* Insert new feature flags introduced by v9 */
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('authorization_server_multistore', 'env,dotenv,db', 'Authorization server - Multistore', 'Admin.Advparameters.Feature', 'Enable or disable the Authorization server when multistore is enabled.', 'Admin.Advparameters.Help', 0, 'beta'),
  ('symfony_layout', 'env,query,dotenv,db', 'Symfony layout', 'Admin.Advparameters.Feature', 'Enable / Disable symfony layout (in opposition to legacy layout).', 'Admin.Advparameters.Help', 1, 'beta'),
  ('front_container_v2', 'env,dotenv,db', 'New front container', 'Admin.Advparameters.Feature', 'Enable / Disable the new front container.', 'Admin.Advparameters.Help', 0, 'beta'),
  ('customer_group', 'env,dotenv,db', 'Customer group', 'Admin.Advparameters.Feature', 'Enable / Disable the customer group page.', 'Admin.Advparameters.Help', 0, 'beta'),
  ('store', 'env,dotenv,db', 'Store', 'Admin.Advparameters.Feature', 'Enable / Disable the store page.', 'Admin.Advparameters.Help', 0, 'beta'),
  ('search_conf', 'env,dotenv,db', 'Search configuration', 'Admin.Advparameters.Feature', 'Enable / Disable the search configuration page.', 'Admin.Advparameters.Help', 0, 'beta'),
  ('merchandise_return', 'env,dotenv,db', 'Merchandise return', 'Admin.Advparameters.Feature', 'Enable / Disable the merchandise return page.', 'Admin.Advparameters.Help', 0, 'beta');

/* Remove old feature flags from 8.1.x */
DELETE FROM `PREFIX_feature_flag` WHERE `name` IN ('product_page_v2', 'title', 'order_state', 'multiple_image_format');

/* Category redirect */
ALTER TABLE `PREFIX_category` ADD `redirect_type` ENUM(
    '404', '410', '301', '302'
) NOT NULL DEFAULT '301';
ALTER TABLE `PREFIX_category` ADD `id_type_redirected`int(10) unsigned NOT NULL DEFAULT '0';
UPDATE `PREFIX_category` SET `redirect_type` = '404' WHERE `is_root_category` = 1;

/* Increase size of customized data - https://github.com/PrestaShop/PrestaShop/pull/31109 */
ALTER TABLE `PREFIX_customized_data` MODIFY `value` varchar(1024) NOT NULL;

/* Request optimization for back office KPI and others */
ALTER TABLE `PREFIX_orders` ADD INDEX `invoice_date` (`invoice_date`);

/* Remove obsolete enable/disable module on mobile feature */
/* https://github.com/PrestaShop/PrestaShop/pull/31151 */
DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_ALLOW_MOBILE_DEVICE';
DELETE FROM `PREFIX_hook` WHERE `name` = 'actionBeforeEnableMobileModule';
DELETE FROM `PREFIX_hook` WHERE `name` = 'actionBeforeDisableMobileModule';
DELETE FROM `PREFIX_hook_module` WHERE `id_hook` NOT IN (SELECT id_hook FROM `PREFIX_hook`);
DELETE FROM `PREFIX_hook_module_exceptions` WHERE `id_hook` NOT IN (SELECT id_hook FROM `PREFIX_hook`);
UPDATE `PREFIX_module_shop` SET `enable_device` = '7';

/* Allow cover configuration */
/* https://github.com/PrestaShop/PrestaShop/pull/33363 */
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES ('PS_USE_COMBINATION_IMAGE_IN_LISTING', '0', NOW(), NOW());

/* Remove purpose of store */
/* https://github.com/PrestaShop/PrestaShop/pull/33232 */
DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_SHOP_ACTIVITY';

/* Remove advanced stock management remains */
/* https://github.com/PrestaShop/PrestaShop/pull/33158 */
/* Remove configuration */
DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_STOCK_MVT_REASON_DEFAULT';
DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_STOCK_MVT_INC_REASON_DEFAULT';
DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_STOCK_MVT_DEC_REASON_DEFAULT';
DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_ADVANCED_STOCK_MANAGEMENT';
DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_STOCK_MVT_TRANSFER_TO';
DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_STOCK_MVT_TRANSFER_FROM';
DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_STOCK_MVT_SUPPLY_ORDER';
DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_SSL_ENABLED_EVERYWHERE';
/* Remove authorization roles and all assignments to profiles */
DELETE FROM `PREFIX_authorization_role` WHERE `slug` = 'ROLE_MOD_TAB_ADMINPARENTSTOCKMANAGEMENT_CREATE';
DELETE FROM `PREFIX_authorization_role` WHERE `slug` = 'ROLE_MOD_TAB_ADMINPARENTSTOCKMANAGEMENT_READ';
DELETE FROM `PREFIX_authorization_role` WHERE `slug` = 'ROLE_MOD_TAB_ADMINPARENTSTOCKMANAGEMENT_UPDATE';
DELETE FROM `PREFIX_authorization_role` WHERE `slug` = 'ROLE_MOD_TAB_ADMINPARENTSTOCKMANAGEMENT_DELETE';
DELETE FROM `PREFIX_authorization_role` WHERE `slug` = 'ROLE_MOD_TAB_ADMINSTOCK_CREATE';
DELETE FROM `PREFIX_authorization_role` WHERE `slug` = 'ROLE_MOD_TAB_ADMINSTOCK_READ';
DELETE FROM `PREFIX_authorization_role` WHERE `slug` = 'ROLE_MOD_TAB_ADMINSTOCK_UPDATE';
DELETE FROM `PREFIX_authorization_role` WHERE `slug` = 'ROLE_MOD_TAB_ADMINSTOCK_DELETE';
DELETE FROM `PREFIX_authorization_role` WHERE `slug` = 'ROLE_MOD_TAB_ADMINWAREHOUSES_CREATE';
DELETE FROM `PREFIX_authorization_role` WHERE `slug` = 'ROLE_MOD_TAB_ADMINWAREHOUSES_READ';
DELETE FROM `PREFIX_authorization_role` WHERE `slug` = 'ROLE_MOD_TAB_ADMINWAREHOUSES_UPDATE';
DELETE FROM `PREFIX_authorization_role` WHERE `slug` = 'ROLE_MOD_TAB_ADMINWAREHOUSES_DELETE';
DELETE FROM `PREFIX_access` WHERE `id_authorization_role` NOT IN (SELECT id_authorization_role FROM `PREFIX_authorization_role`);
/* Remove all menu tabs related to deleted controllers */
DELETE FROM `PREFIX_tab` WHERE `class_name` = 'AdminStock';
DELETE FROM `PREFIX_tab` WHERE `class_name` = 'AdminWarehouses';
DELETE FROM `PREFIX_tab` WHERE `class_name` = 'AdminParentStockManagement';
DELETE FROM `PREFIX_tab` WHERE `class_name` = 'AdminStockMvt';
DELETE FROM `PREFIX_tab` WHERE `class_name` = 'AdminStockInstantState';
DELETE FROM `PREFIX_tab` WHERE `class_name` = 'AdminStockCover';
DELETE FROM `PREFIX_tab` WHERE `class_name` = 'AdminSupplyOrders';
DELETE FROM `PREFIX_tab` WHERE `class_name` = 'AdminStockConfiguration';
-- Avoid Error Code: 1093 by nesting subrequest
DELETE FROM `PREFIX_tab` WHERE `id_parent` > 0 AND `id_parent` NOT IN (SELECT `id_tab` FROM (SELECT `id_tab` FROM `PREFIX_tab`) as c);
DELETE FROM `PREFIX_tab_lang` WHERE `id_tab` NOT IN (SELECT `id_tab` FROM `PREFIX_tab`);

/* Change the length of the ean13 field */
ALTER TABLE `PREFIX_product` MODIFY COLUMN `ean13` VARCHAR(20);
ALTER TABLE `PREFIX_order_detail` MODIFY COLUMN `product_ean13` VARCHAR(20);
ALTER TABLE `PREFIX_product_attribute` MODIFY COLUMN `ean13` VARCHAR(20);
ALTER TABLE `PREFIX_stock` MODIFY COLUMN `ean13` VARCHAR(20);
ALTER TABLE `PREFIX_supply_order_detail` MODIFY COLUMN `ean13` VARCHAR(20);

/* Change all empty string to 'default' */
UPDATE `PREFIX_product` SET `redirect_type` = 'default' WHERE `redirect_type` = '';
UPDATE `PREFIX_product_shop` SET `redirect_type` = 'default' WHERE `redirect_type` = '';

ALTER TABLE `PREFIX_product` MODIFY COLUMN `redirect_type` ENUM(
    '404','410','301-product','302-product','301-category','302-category','200-displayed','404-displayed','410-displayed','default'
    ) NOT NULL DEFAULT 'default';
ALTER TABLE `PREFIX_product_shop` MODIFY COLUMN `redirect_type` ENUM(
    '404','410','301-product','302-product','301-category','302-category','200-displayed','404-displayed','410-displayed','default'
    ) NOT NULL DEFAULT 'default';


/* Fixing duplicates for table "accessory" where can be duplicate records from older version of PrestaShop, because of missing PRIMARY index */
CREATE TABLE `PREFIX_accessory_tmp` SELECT DISTINCT `id_product_1`, `id_product_2` FROM `PREFIX_accessory`;
ALTER TABLE `PREFIX_accessory_tmp` ADD CONSTRAINT accessory_product PRIMARY KEY (`id_product_1`, `id_product_2`);
DROP TABLE `PREFIX_accessory`;
RENAME TABLE `PREFIX_accessory_tmp` TO `PREFIX_accessory`;

ALTER TABLE `PREFIX_stock_mvt` MODIFY `id_supply_order` INT(11) DEFAULT '0';

DROP TABLE IF EXISTS `PREFIX_api_access`;
DROP TABLE IF EXISTS `PREFIX_authorized_application`;
CREATE TABLE `PREFIX_api_client`
(
     `id_api_client` int(10) unsigned NOT NULL AUTO_INCREMENT,
     `client_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
     `client_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
     `client_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
     `enabled` tinyint(1) NOT NULL,
     `scopes` json NOT NULL,
     `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
     `external_issuer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
     `lifetime` int(11) NOT NULL DEFAULT '3600',
     PRIMARY KEY (`id_api_client`),
     UNIQUE KEY `api_client_client_id_idx` (`client_id`,`external_issuer`),
     UNIQUE KEY `api_client_client_name_idx` (`client_name`,`external_issuer`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `PREFIX_image_type`
    CHANGE `id_image_type` `id_image_type` int(10) unsigned NOT NULL AUTO_INCREMENT,
    CHANGE `width` `width` int(10) unsigned NOT NULL,
    CHANGE `height` `height` int(10) unsigned NOT NULL,
    CHANGE `products` `products`  tinyint(1) NOT NULL DEFAULT '1',
    CHANGE `manufacturers` `manufacturers`  tinyint(1) NOT NULL DEFAULT '1',
    CHANGE `stores` `stores` tinyint(1) NOT NULL DEFAULT '1',
    DROP key `image_type_name`,
    ADD UNIQUE KEY `UNIQ_907C95215E237E06` (`name`);

CREATE TABLE `PREFIX_mutation` (
   `id_mutation` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `mutation_table` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
   `mutation_row_id` bigint(20) NOT NULL,
   `mutation_action` enum('create','update','delete') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   `mutator_type` enum('employee','api_client','module') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   `mutator_identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
   `mutation_details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   `date_add` datetime NOT NULL,
   PRIMARY KEY (`id_mutation`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `PREFIX_access` ADD KEY `IDX_564352A15FCA037F` (`id_profile`);
ALTER TABLE `PREFIX_access` ADD KEY `IDX_564352A18C6DE0E5` (`id_authorization_role`);
ALTER TABLE `PREFIX_accessory` CHARSET=utf8mb4;
ALTER TABLE `PREFIX_employee` ADD KEY `IDX_1D8DF9EBBA299860` (`id_lang`);
ALTER TABLE `PREFIX_employee_session` ADD KEY `IDX_B10E26A1D449934` (`id_employee`);
ALTER TABLE `PREFIX_product_download` ADD KEY `product_active` (`id_product`,`active`);
ALTER TABLE `PREFIX_product_download` ADD UNIQUE KEY `id_product` (`id_product`);

ALTER TABLE `PREFIX_shop_url` CHANGE `id_shop_url` `id_shop_url` int(11) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `PREFIX_shop_url` CHANGE `id_shop` `id_shop` int(11) unsigned NOT NULL;
ALTER TABLE `PREFIX_shop_url` ADD UNIQUE KEY `full_shop_url` (`domain`,`physical_uri`,`virtual_uri`);
ALTER TABLE `PREFIX_shop_url` ADD UNIQUE KEY `full_shop_url_ssl` (`domain_ssl`,`physical_uri`,`virtual_uri`);
ALTER TABLE `PREFIX_shop_url` ADD KEY `id_shop` (`id_shop`,`main`);
