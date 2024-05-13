/* script intended for catching up with requests forgotten since 1.7 */

/* 1.7.1.0 */
ALTER TABLE `PREFIX_product` CHANGE `id_type_redirected` `id_type_redirected` INT(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `PREFIX_product_shop` CHANGE `id_type_redirected` `id_type_redirected` INT(10) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `PREFIX_tab` CHANGE `active` `active` TINYINT(1) NOT NULL;
ALTER TABLE `PREFIX_tab` CHANGE `icon` `icon` VARCHAR(32) DEFAULT NULL;

/* PHP:add_missing_unique_key_from_authorization_role(); */;

/* 1.7.2.0 */
ALTER TABLE `PREFIX_stock_mvt` CHANGE `sign` `sign` SMALLINT(6) NOT NULL DEFAULT '1';
ALTER TABLE `PREFIX_carrier_lang` CHANGE `delay` `delay` VARCHAR(512) DEFAULT NULL;

/* 1.7.5.0 */
ALTER TABLE `PREFIX_manufacturer_lang` CHANGE `meta_title` `meta_title` VARCHAR(255) DEFAULT NULL;
UPDATE `PREFIX_stock` SET `reference` = '' WHERE `reference` IS NULL;
ALTER TABLE `PREFIX_stock` CHANGE `reference` `reference` VARCHAR(64) NOT NULL;

/* 1.7.6.0 */
ALTER TABLE `PREFIX_currency` CHANGE `numeric_iso_code` `numeric_iso_code` VARCHAR(3) DEFAULT NULL AFTER `iso_code`;

/* 1.7.8.0 */
DROP TABLE IF EXISTS `PREFIX_order_slip_detail_tax`;

/* 8.0.0 */
DROP TABLE IF EXISTS `PREFIX_attribute_impact`;
/* PHP:drop_column_if_exists('orders', 'shipping_number'); */;
