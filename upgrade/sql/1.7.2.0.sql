SET SESSION sql_mode = '';
SET NAMES 'utf8';

ALTER TABLE `PREFIX_store` MODIFY `hours` text;

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'displayAdminProductsMainStepLeftColumnMiddle', 'Display new elements in back office product page, left column of the Basic settings tab', 'This hook launches modules when the back office product page is displayed', '1'),
  (NULL, 'displayAdminProductsMainStepLeftColumnBottom', 'Display new elements in back office product page, left column of the Basic settings tab', 'This hook launches modules when the back office product page is displayed', '1'),
  (NULL, 'displayAdminProductsMainStepRightColumnBottom', 'Display new elements in back office product page, right column of the Basic settings tab', 'This hook launches modules when the back office product page is displayed', '1'),
  (NULL, 'displayAdminProductsQuantitiesStepBottom', 'Display new elements in back office product page, Quantities/Combinations tab', 'This hook launches modules when the back office product page is displayed', '1'),
  (NULL, 'displayAdminProductsPriceStepBottom', 'Display new elements in back office product page, Price tab', 'This hook launches modules when the back office product page is displayed', '1'),
  (NULL, 'displayAdminProductsOptionsStepTop', 'Display new elements in back office product page, Options tab', 'This hook launches modules when the back office product page is displayed', '1'),
  (NULL, 'displayAdminProductsOptionsStepBottom', 'Display new elements in back office product page, Options tab', 'This hook launches modules when the back office product page is displayed', '1'),
  (NULL, 'displayAdminProductsSeoStepBottom', 'Display new elements in back office product page, SEO tab', 'This hook launches modules when the back office product page is displayed', '1'),
  (NULL, 'displayAdminProductsShippingStepBottom', 'Display new elements in back office product page, Shipping tab', 'This hook launches modules when the back office product page is displayed', '1'),
  (NULL, 'displayAdminProductsCombinationBottom', 'Display new elements in back office product page, Combination tab', 'This hook launches modules when the back office product page is displayed', '1'),
  (NULL, 'displayWrapperTop', 'Main wrapper section (top)', 'This hook displays new elements in the top of the main wrapper', '1'),
  (NULL, 'displayWrapperBottom', 'Main wrapper section (bottom)', 'This hook displays new elements in the bottom of the main wrapper', '1'),
  (NULL, 'displayContentWrapperTop', 'Content wrapper section (top)', 'This hook displays new elements in the top of the content wrapper', '1'),
  (NULL, 'displayContentWrapperBottom', 'Content wrapper section (bottom)', 'This hook displays new elements in the bottom of the content wrapper', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

/* PHP:drop_column_from_product_lang_if_exists(); */;

ALTER TABLE `PREFIX_product` CHANGE `isbn` `isbn` VARCHAR(32) NULL DEFAULT NULL;
ALTER TABLE `PREFIX_order_detail` CHANGE `product_isbn` `product_isbn` VARCHAR(32) NULL DEFAULT NULL;
ALTER TABLE `PREFIX_product_attribute` CHANGE `isbn` `isbn` VARCHAR(32) NULL DEFAULT NULL;
ALTER TABLE `PREFIX_stock` CHANGE `isbn` `isbn` VARCHAR(32) NULL DEFAULT NULL;
ALTER TABLE `PREFIX_supply_order_detail` CHANGE `isbn` `isbn` VARCHAR(32) NULL DEFAULT NULL;

ALTER TABLE `PREFIX_stock_available` ADD `physical_quantity` INT NOT NULL DEFAULT '0' AFTER `quantity`;
ALTER TABLE `PREFIX_stock_available` ADD `reserved_quantity` INT NOT NULL DEFAULT '0' AFTER `physical_quantity`;
ALTER TABLE `PREFIX_stock_mvt` COLLATE=utf8_unicode_ci;
ALTER TABLE `PREFIX_stock_mvt` CHANGE `id_stock` `id_stock` INT(11) NOT NULL COMMENT 'since ps 1.7 corresponding to id_stock_available';
ALTER TABLE `PREFIX_stock_mvt` CHANGE `id_stock_mvt` `id_stock_mvt` BIGINT(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `PREFIX_stock_mvt` CHANGE `id_order` `id_order` INT(11) DEFAULT NULL;
ALTER TABLE `PREFIX_stock_mvt` CHANGE `id_supply_order` `id_supply_order` INT(11) DEFAULT NULL;
ALTER TABLE `PREFIX_stock_mvt` CHANGE `id_stock_mvt_reason` `id_stock_mvt_reason` INT(11) NOT NULL;
ALTER TABLE `PREFIX_stock_mvt` CHANGE `id_employee` `id_employee` INT(11) NOT NULL;
ALTER TABLE `PREFIX_stock_mvt` CHANGE `physical_quantity` `physical_quantity` INT(11) NOT NULL;
ALTER TABLE `PREFIX_stock_mvt` CHANGE `referer` `referer` BIGINT(20) DEFAULT NULL;
ALTER TABLE `PREFIX_stock_mvt` CHANGE `employee_lastname` `employee_lastname` varchar(32) DEFAULT NULL;
ALTER TABLE `PREFIX_stock_mvt` CHANGE `employee_firstname` `employee_firstname` varchar(32) DEFAULT NULL;
ALTER TABLE `PREFIX_stock_mvt` CHANGE `sign` `sign` smallint(6) NOT NULL DEFAULT '1';

UPDATE `PREFIX_configuration` SET `value` = 0 WHERE `name` = "PS_ADVANCED_STOCK_MANAGEMENT";
/* PHP:add_new_status_stock(); */;

ALTER TABLE `PREFIX_carrier_lang` CHANGE `delay` `delay` varchar(512) DEFAULT NULL;
