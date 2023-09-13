SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

UPDATE `PREFIX_configuration` SET `value` = 'US/Pacific' WHERE `name` = 'PS_TIMEZONE' AND `value` = 'US/Pacific-New' LIMIT 1;
DELETE FROM `PREFIX_timezone` WHERE `name` = 'US/Pacific-New';

INSERT IGNORE INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionModifyFrontendSitemap', 'Add or remove links on sitemap page', 'This hook allows to modify links on sitemap page of your shop. Useful to improve indexation of your modules.', '1'),
  (NULL, 'displayAddressSelectorBottom', 'After address selection on checkout page', 'This hook is displayed after the address selection in checkout step.', '1'),
  (NULL, 'actionGenerateDocumentReference', 'Modify document reference', 'This hook allows modules to return custom document references', '1'),
  (NULL, 'actionLoggerLogMessage', 'Allows to make extra action while a log is triggered', 'This hook allows to make an extra action while an exception is thrown and the logger logs it', '1'),
  (NULL, 'actionProductPriceCalculation', 'Product Price Calculation', 'This hook is called into the priceCalculation method to be able to override the price calculation', '1');

INSERT IGNORE INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionAdminMenuTabsModifier', 'Modify back office menu', 'This hook allows modifying back office menu tabs', '1');

/* Default configuration for backorder, in order to keep behavior */
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
  ('PS_ENABLE_BACKORDER_STATUS', '1', NOW(), NOW());

/* Keep sending e-mails with prefixed subject to avoid behaviour change */
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
  ('PS_MAIL_SUBJECT_PREFIX', '1', NOW(), NOW());

/* Add new product_attribute_lang table and fill it with data */
CREATE TABLE `PREFIX_product_attribute_lang` (
  `id_product_attribute` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `available_now` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `available_later` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_product_attribute`, `id_lang`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `PREFIX_product_attribute_lang`
(id_product_attribute, id_lang, available_now, available_later)
SELECT pa.id_product_attribute, l.id_lang, '', ''
FROM `PREFIX_product_attribute` pa CROSS JOIN `PREFIX_lang` l;

/* Add default redirect configuration */
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
  ('PS_PRODUCT_REDIRECTION_DEFAULT', '404', NOW(), NOW()),
  ('PS_MAINTENANCE_ALLOW_ADMINS', 1, NOW(), NOW()),
  ('PS_AVIF_QUALITY', '90', NOW(), NOW()),
  ('PS_IMAGE_FORMAT', 'jpg', NOW(), NOW())
;

/* Update ENUM values in both tables*/
ALTER TABLE `PREFIX_product` MODIFY COLUMN `redirect_type` ENUM(
  '','404','410','301-product','302-product','301-category','302-category','200-displayed','404-displayed','410-displayed','default'
) NOT NULL DEFAULT 'default';
ALTER TABLE `PREFIX_product_shop` MODIFY COLUMN `redirect_type` ENUM(
  '','404','410','301-product','302-product','301-category','302-category','200-displayed','404-displayed','410-displayed','default'
) NOT NULL DEFAULT 'default';

/* and change all '404' to 'default' */
UPDATE `PREFIX_product` SET `redirect_type` = 'default' WHERE `redirect_type` = '404' OR `redirect_type` = '' OR `redirect_type` IS NULL;
UPDATE `PREFIX_product_shop` SET `redirect_type` = 'default' WHERE `redirect_type` = '404' OR `redirect_type` = '' OR `redirect_type` IS NULL;

/* Update feature flags */
/* PHP:ps_810_update_product_page_feature_flags(); */;

/* add new feature flag for multiple image formats */
INSERT INTO `PREFIX_feature_flag` (`name`, `state`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `stability`)
VALUES
    ('multiple_image_format', 0, 'Multiple image formats', 'Admin.Advparameters.Feature', 'Enable / Disable having more than one image format (jpg, webp, avif, png...)', 'Admin.Advparameters.Help', 'stable');

ALTER TABLE `PREFIX_stock_mvt` CHANGE `employee_lastname` `employee_lastname` VARCHAR(255) DEFAULT NULL, CHANGE `employee_firstname` `employee_firstname` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `PREFIX_stock_mvt` CHANGE `physical_quantity` `physical_quantity` INT(10) UNSIGNED NOT NULL;

/* PHP:add_hook('actionAdminBreadcrumbModifier', 'Modify back office breadcrumb', 'This hook allows modifying back office breadcrumb'); */;

ALTER TABLE `PREFIX_order_payment` ADD `id_employee` INT NULL AFTER `date_add`;
