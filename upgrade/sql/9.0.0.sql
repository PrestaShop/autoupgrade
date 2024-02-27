SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
  ('PS_DEBUG_COOKIE_NAME', '', NOW(), NOW()),
  ('PS_DEBUG_COOKIE_VALUE', '', NOW(), NOW())
;

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionUpdateCartAddress', 'Triggers after changing address on the cart', 'This hook is called after address is changed on the cart', '1'),
  (NULL, 'actionPresentCategory', 'Category Presenter', 'This hook is called before a category is presented', '1'),
  (NULL, 'actionPresentStore', 'Store Presenter', 'This hook is called before a store is presented', '1'),
  (NULL, 'actionPresentSupplier', 'Supplier Presenter', 'This hook is called before a supplier is presented', '1'),
  (NULL, 'actionPresentManufacturer', 'Manufacturer Presenter', 'This hook is called before a manufacturer is presented', '1'),
  (NULL, 'actionCartGetPackageShippingCost', 'After getting package shipping cost value', 'This hook is called in order to allow to modify package shipping cost', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

ALTER TABLE `PREFIX_feature_flag` ADD `type` VARCHAR(64) DEFAULT 'env,dotenv,db' NOT NULL AFTER `name`;

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
DELETE FROM `PREFIX_tab_lang` WHERE `id_tab` NOT IN (SELECT id_tab FROM `PREFIX_tab`);
