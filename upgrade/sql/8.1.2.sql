SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/*
Security section tabs were correctly added (ps_800_add_security_tab.php) for people coming from 1.7.8,
but had missing wordings on new 8.0.0-8.1.1 installs.
We fixed it for people installing fresh 8.1.2, but we also need to fix it for people that started on 8.0.0-8.1.1 versions.
*/
UPDATE `PREFIX_tab` SET wording_domain = 'Admin.Navigation.Menu', wording = 'Security' WHERE class_name = 'AdminParentSecurity';
UPDATE `PREFIX_tab` SET wording_domain = 'Admin.Navigation.Menu', wording = 'Employee Sessions' WHERE class_name = 'AdminSecuritySessionEmployee';
UPDATE `PREFIX_tab` SET wording_domain = 'Admin.Navigation.Menu', wording = 'Customer Sessions' WHERE class_name = 'AdminSecuritySessionCustomer';

INSERT IGNORE INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionLanguageLinkParameters', 'Add parameters to language link', 'Allows modules to provide proper parameters for links in other languages.', '1'),
  (NULL, 'actionAfterLoadRoutes', 'Triggers after loading routes', 'Allow modules to modify routes in any way or add their own multilanguage routes.', '1');

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
