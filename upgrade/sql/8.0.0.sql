SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

DROP TABLE IF EXISTS `PREFIX_referrer`;
DROP TABLE IF EXISTS `PREFIX_referrer_cache`;
DROP TABLE IF EXISTS `PREFIX_referrer_shop`;

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
  (NULL, 'displayAdminThemesListAfter', 'BO themes list extra content', 'This hook displays content after the themes list in the BackOffice', '1'),
  (NULL, 'displayModuleConfigureExtraButtons', 'Module configuration - After toolbar buttons', 'This hook allows to add toolbar''s additional content on module configuration page', '1')
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
