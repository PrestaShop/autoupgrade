SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/* Change the ape field lenght to match all code formats */
ALTER TABLE `PREFIX_customer` CHANGE `ape` `ape` varchar(6) DEFAULT NULL;

/* We fixed some issues in older upgrade scripts. These are here to fix stores that have been upgraded in the meantime. */
/* PHP:drop_column_if_exists('product_attribute', 'location'); */;
/* PHP:drop_column_if_exists('product_attribute', 'quantity'); */;
ALTER TABLE `PREFIX_smarty_lazy_cache` CHANGE `cache_id` `cache_id` VARCHAR(191) NOT NULL DEFAULT '';
ALTER TABLE `PREFIX_log` CHANGE `id_shop` `id_shop` INT(10) unsigned DEFAULT NULL;
ALTER TABLE `PREFIX_log` CHANGE `id_shop_group` `id_shop_group` INT(10) unsigned DEFAULT NULL;
ALTER TABLE `PREFIX_log` CHANGE `id_lang` `id_lang` INT(10) unsigned DEFAULT NULL;
ALTER TABLE `PREFIX_product_attribute_lang` CHANGE `available_now` `available_now` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `PREFIX_product_attribute_lang` CHANGE `available_later` `available_later` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `PREFIX_order_cart_rule` CHANGE `deleted` `deleted` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `PREFIX_product_group_reduction_cache` CHANGE `reduction` `reduction` DECIMAL(5, 4) NOT NULL;
ALTER TABLE `PREFIX_stock_mvt` CHANGE `physical_quantity` `physical_quantity` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `PREFIX_group_reduction` CHANGE `reduction` `reduction` DECIMAL(5, 4) NOT NULL;
ALTER TABLE `PREFIX_order_payment` CHANGE `amount` `amount` DECIMAL(20, 6) NOT NULL;

/* Minimal_quantity should be 1 at least, not 0 */
UPDATE `PREFIX_product` SET `minimal_quantity` = 1 WHERE `minimal_quantity` = 0;
UPDATE `PREFIX_product_shop` SET `minimal_quantity` = 1 WHERE `minimal_quantity` = 0;
UPDATE `PREFIX_product_attribute` SET `minimal_quantity` = 1 WHERE `minimal_quantity` = 0;
UPDATE `PREFIX_product_attribute_shop` SET `minimal_quantity` = 1 WHERE `minimal_quantity` = 0;

/* Normalize some older database records that should not be NULL, prevents errors in new code. */
/* Mainly concerns people coming all the way from 1.6, but will fix many inconsitencies. */

/* PHP:update_null_values(); */;
