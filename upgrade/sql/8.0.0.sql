SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

DROP TABLE IF EXISTS `PREFIX_referrer`;
DROP TABLE IF EXISTS `PREFIX_referrer_cache`;
DROP TABLE IF EXISTS `PREFIX_referrer_shop`;

/* Remove page Referrers */
## Remove Tabs
DELETE FROM `PREFIX_tab` WHERE `class_name` = 'AdminReferrers';
DELETE FROM `PREFIX_tab_lang` WHERE `id_tab` NOT IN (SELECT id_tab FROM `PREFIX_tab`);
## Remove Roles
DELETE FROM `PREFIX_access` WHERE `id_tab` NOT IN (SELECT id_tab FROM `PREFIX_tab`);
/* For SalesMan profile, remove parent tab `Traffic & SEO` */
DELETE FROM `PREFIX_access`
  WHERE `id_authorization_role` IN (SELECT `id_authorization_role` FROM `PREFIX_authorization_role` WHERE `slug` LIKE 'ROLE_MOD_TAB_ADMINPARENTMETA_%')
  AND `id_profile` = 4;
DELETE FROM `PREFIX_authorization_role`
  WHERE `slug` LIKE 'ROLE_MOD_TAB_ADMINREFERRERS_%';
## Remove Configuration
DELETE FROM `PREFIX_configuration`
  WHERE `name` IN ('PS_REFERRERS_CACHE_LIKE', 'PS_REFERRERS_CACHE_DATE');
## Remove Quick Access
DELETE FROM `PREFIX_quick_access_lang`
  WHERE id_quick_access IN (
    SELECT id_quick_access FROM `PREFIX_quick_access` WHERE link LIKE '%controller=AdminReferrers%'
  );
DELETE FROM `PREFIX_quick_access`
  WHERE link LIKE '%controller=AdminReferrers%';

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
    ('PS_MAIL_DKIM_ENABLE', '0', NOW(), NOW()),
    ('PS_MAIL_DKIM_DOMAIN', '', NOW(), NOW()),
    ('PS_MAIL_DKIM_SELECTOR', '', NOW(), NOW()),
    ('PS_MAIL_DKIM_KEY', '', NOW(), NOW()),
    ('PS_WEBP_QUALITY', '80', NOW(), NOW()),
    ('PS_SECURITY_TOKEN', '1', NOW(), NOW())
;

INSERT IGNORE INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionValidateOrderAfter', 'New Order', 'This hook is called after validating an order by core', '1'),
  (NULL, 'actionAdminOrdersTrackingNumberUpdate', 'After setting the tracking number for the order', 'This hook allows you to execute code after the unique tracking number for the order was added', '1'),
  (NULL, 'actionAdminSecurityControllerPostProcessBefore', 'On post-process in Admin Security Controller', 'This hook is called on Admin Security Controller post-process before processing any form', '1'),
  (NULL, 'actionAdminSecurityControllerPostProcessGeneralBefore', 'On post-process in Admin Security Controller', 'This hook is called on Admin Security Controller post-process before processing the General form', '1'),
  (NULL, 'dashboardZoneThree', 'Dashboard column three', 'This hook is displayed in the third column of the dashboard', '1'),
  (NULL, 'actionPresentPaymentOptions', 'Payment options Presenter', 'This hook is called before payment options are presented', '1'),
  (NULL, 'actionCustomerLogoutBefore', 'Before customer logout', 'This hook allows you to execute code before customer logout', '1'),
  (NULL, 'actionCustomerLogoutAfter', 'After customer logout', 'This hook allows you to execute code after customer logout', '1'),
  (NULL, 'displayCheckoutBeforeConfirmation', 'Show custom content before checkout confirmation', 'This hook allows you to display custom content at the end of checkout process', '1'),
  (NULL, 'displayCheckoutSummaryTop', 'Cart summary top', 'This hook allows you to display new elements in top of cart summary', '1'),
  (NULL, 'displayAdminThemesListAfter', 'BO themes list extra content', 'This hook displays content after the themes list in the back office', '1'),
  (NULL, 'displayModuleConfigureExtraButtons', 'Module configuration - After toolbar buttons', 'This hook allows to add toolbar''s additional content on module configuration page', '1'),
  (NULL, 'actionGetAlternativeSearchPanels', 'Additional search panel', 'This hook allows to add an additional search panel for external providers in PrestaShop back office', '1')
;

ALTER TABLE `PREFIX_employee_session` ADD `date_upd` DATETIME NOT NULL AFTER `token`;
ALTER TABLE `PREFIX_employee_session` ADD `date_add` DATETIME NOT NULL AFTER `date_upd`;
ALTER TABLE `PREFIX_customer_session` ADD `date_upd` DATETIME NOT NULL AFTER `token`;
ALTER TABLE `PREFIX_customer_session` ADD `date_add` DATETIME NOT NULL AFTER `date_upd`;

ALTER TABLE `PREFIX_carrier` DROP COLUMN `id_tax_rules_group`;

ALTER TABLE `PREFIX_category_lang` ADD `additional_description` text AFTER `description`;

ALTER TABLE `PREFIX_product` MODIFY COLUMN `redirect_type` ENUM(
    '404', '410', '301-product', '302-product', '301-category', '302-category'
) NOT NULL DEFAULT '404';
ALTER TABLE `PREFIX_product_shop` MODIFY COLUMN `redirect_type` ENUM(
    '404', '410', '301-product', '302-product', '301-category', '302-category'
) NOT NULL DEFAULT '404';

/* PHP:ps_800_add_security_tab(); */;

ALTER TABLE `PREFIX_order_detail` MODIFY COLUMN `product_name` TEXT NOT NULL;

ALTER TABLE `PREFIX_product` ADD `unit_price` decimal(20, 6) NOT NULL DEFAULT '0.000000' AFTER `unity`;
ALTER TABLE `PREFIX_product_shop` ADD `unit_price` decimal(20, 6) NOT NULL DEFAULT '0.000000' AFTER `unity`;

UPDATE `PREFIX_product` SET `unit_price` = IF (`unit_price_ratio` != 0, `price` / `unit_price_ratio`, 0);
UPDATE `PREFIX_product_shop` SET `unit_price` = IF (`unit_price_ratio` != 0, `price` / `unit_price_ratio`, 0);


ALTER TABLE `PREFIX_feature_flag` ADD `stability` VARCHAR(64) DEFAULT 'beta' NOT NULL;
UPDATE `PREFIX_feature_flag` SET `state` = '0', `stability` = 'stable', `label_wording` = 'New product page - Single store', `description_wording` = 'This page benefits from increased performance and includes new features such as a new combination management system.' WHERE `name` = 'product_page_V2';

INSERT INTO `PREFIX_feature_flag` (`name`, `state`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `stability`)
VALUES ('product_page_v2_multi_shop', '0', 'New product page - Multi store', 'Admin.Advparameters.Feature', 'Access the new product page, even in a multistore context. This is a work in progress and some features are not available.', 'Admin.Advparameters.Help', 'beta');

UPDATE `PREFIX_tab` SET wording = 'New & Experimental Features' WHERE `class_name` = 'AdminFeatureFlag';

/* PHP:ps_update_tab_lang('Admin.Navigation.Menu', 'AdminFeatureFlag'); */;

UPDATE `PREFIX_quick_access` SET `link` = 'index.php/sell/orders' WHERE `link` = 'index.php?controller=AdminOrders';

ALTER TABLE  `PREFIX_stock_mvt` CHANGE `physical_quantity` `physical_quantity` INT(11) UNSIGNED  NOT NULL;
