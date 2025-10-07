SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/* Add a file separator input to the sql manager settings - https://github.com/PrestaShop/PrestaShop/pull/35843 */
/* Allow configuring maximum word difference - https://github.com/PrestaShop/PrestaShop/pull/37261 */
/* PHP:add_configuration_if_not_exists('PS_DEBUG_COOKIE_NAME', ''); */;
/* PHP:add_configuration_if_not_exists('PS_DEBUG_COOKIE_VALUE', ''); */;
/* PHP:add_configuration_if_not_exists('PS_SEPARATOR_FILE_MANAGER_SQL', ';'); */;
/* PHP:add_configuration_if_not_exists('PS_PRODUCT_BREADCRUMB_CATEGORY', 'default'); */;
/* PHP:add_configuration_if_not_exists('PS_SEARCH_FUZZY_MAX_DIFFERENCE', 5); */;

/* Enable controlling of default language URL prefix - https://github.com/PrestaShop/PrestaShop/pull/37236 */
/* PHP:ps_900_set_url_lang_prefix(); */;

/* Remove meta keywords - https://github.com/PrestaShop/PrestaShop/pull/36873 */
/* PHP:drop_column_if_exists('category_lang', 'meta_keywords'); */;
/* PHP:drop_column_if_exists('cms_lang', 'meta_keywords'); */;
/* PHP:drop_column_if_exists('cms_category_lang', 'meta_keywords'); */;
/* PHP:drop_column_if_exists('manufacturer_lang', 'meta_keywords'); */;
/* PHP:drop_column_if_exists('meta_lang', 'keywords'); */;
/* PHP:drop_column_if_exists('product_lang', 'meta_keywords'); */;
/* PHP:drop_column_if_exists('supplier_lang', 'meta_keywords'); */;

/* Add feature flag types */
/* PHP:add_column('feature_flag', 'type', 'VARCHAR(64) DEFAULT \'env,dotenv,db\' NOT NULL AFTER `name`'); */;

/* Insert new feature flags introduced by v9 */
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('front_container_v2', 'env,dotenv,db', 'New front container', 'Admin.Advparameters.Feature', 'Enable / Disable the new front container.', 'Admin.Advparameters.Help', 0, 'beta'),
  ('customer_group', 'env,dotenv,db', 'Customer group', 'Admin.Advparameters.Feature', 'Enable / Disable the customer group page.', 'Admin.Advparameters.Help', 0, 'beta'),
  ('store', 'env,dotenv,db', 'Store', 'Admin.Advparameters.Feature', 'Enable / Disable the store page.', 'Admin.Advparameters.Help', 0, 'beta'),
  ('merchandise_return', 'env,dotenv,db', 'Merchandise return', 'Admin.Advparameters.Feature', 'Enable / Disable the merchandise return page.', 'Admin.Advparameters.Help', 0, 'beta'),
  ('admin_api_multistore', 'env,query,dotenv,db', 'Admin API - Multistore', 'Admin.Advparameters.Feature', 'Enable or disable the Admin API when multistore is enabled.', 'Admin.Advparameters.Help', 0, 'beta'),
  ('admin_api_experimental_endpoints', 'env,dotenv,db', 'Admin API - Enable experimental endpoints', 'Admin.Advparameters.Feature', 'Experimental API endpoints are disabled by default in prod environment, this configuration allows to forcefully enable them.', 'Admin.Advparameters.Help', 0, 'beta')
;

/* Remove olf feature flag before Authorization server was renamed into Admin API */
DELETE FROM `PREFIX_feature_flag` WHERE `name`='authorization_server';

/* Update carrier feature flag to stable, but we don't force enabled by default */
UPDATE `PREFIX_feature_flag` SET `stability` = 'stable' WHERE `name` = 'carrier';

/* Remove old feature flags from 8.1.x */
DELETE FROM `PREFIX_feature_flag` WHERE `name` IN ('product_page_v2', 'title', 'order_state', 'multiple_image_format', 'attribute_group');

/* Category redirect */
/* PHP:add_column('category', 'redirect_type', 'ENUM(\'404\', \'410\', \'301\', \'302\') NOT NULL DEFAULT \'301\''); */;
/* PHP:add_column('category', 'id_type_redirected', 'int(10) unsigned NOT NULL DEFAULT \'0\''); */;
UPDATE `PREFIX_category` SET `redirect_type` = '404' WHERE `is_root_category` = 1;

/* Increase size of customized data - https://github.com/PrestaShop/PrestaShop/pull/31109 */
ALTER TABLE `PREFIX_customized_data` MODIFY `value` varchar(1024) NOT NULL;

/* Request optimization for back office KPI and others */
ALTER TABLE `PREFIX_orders` ADD INDEX `invoice_date` (`invoice_date`);

/* Remove obsolete enable/disable module on mobile feature, obsolete hooks are removed below */
/* https://github.com/PrestaShop/PrestaShop/pull/31151 */
DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_ALLOW_MOBILE_DEVICE';
UPDATE `PREFIX_module_shop` SET `enable_device` = '7';

/* Allow cover configuration */
/* https://github.com/PrestaShop/PrestaShop/pull/33363 */
/* PHP:add_configuration_if_not_exists('PS_USE_COMBINATION_IMAGE_IN_LISTING', '0'); */;

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
/* https://github.com/PrestaShop/PrestaShop/pull/35697 */
ALTER TABLE `PREFIX_product` MODIFY COLUMN `ean13` VARCHAR(20);
ALTER TABLE `PREFIX_order_detail` MODIFY COLUMN `product_ean13` VARCHAR(20);
ALTER TABLE `PREFIX_product_attribute` MODIFY COLUMN `ean13` VARCHAR(20);
ALTER TABLE `PREFIX_stock` MODIFY COLUMN `ean13` VARCHAR(20);
ALTER TABLE `PREFIX_supply_order_detail` MODIFY COLUMN `ean13` VARCHAR(20);

/* Change all empty string to 'default' */
/* https://github.com/PrestaShop/PrestaShop/pull/35996 */
UPDATE `PREFIX_product` SET `redirect_type` = 'default' WHERE `redirect_type` = '';
UPDATE `PREFIX_product_shop` SET `redirect_type` = 'default' WHERE `redirect_type` = '';
ALTER TABLE `PREFIX_product` MODIFY COLUMN `redirect_type` ENUM(
    '404','410','301-product','302-product','301-category','302-category','200-displayed','404-displayed','410-displayed','default'
    ) NOT NULL DEFAULT 'default';
ALTER TABLE `PREFIX_product_shop` MODIFY COLUMN `redirect_type` ENUM(
    '404','410','301-product','302-product','301-category','302-category','200-displayed','404-displayed','410-displayed','default'
    ) NOT NULL DEFAULT 'default';

/* Fixing duplicates for table "accessory" where can be duplicate records from older version of PrestaShop, because of missing PRIMARY index */
/* https://github.com/PrestaShop/PrestaShop/pull/34530 */
CREATE TABLE `PREFIX_accessory_tmp` SELECT DISTINCT `id_product_1`, `id_product_2` FROM `PREFIX_accessory`;
ALTER TABLE `PREFIX_accessory_tmp` ADD CONSTRAINT accessory_product PRIMARY KEY (`id_product_1`, `id_product_2`);
DROP TABLE `PREFIX_accessory`;
RENAME TABLE `PREFIX_accessory_tmp` TO `PREFIX_accessory`;

ALTER TABLE `PREFIX_stock_mvt` MODIFY `id_supply_order` INT(11) DEFAULT '0';

DROP TABLE IF EXISTS `PREFIX_api_access`;
DROP TABLE IF EXISTS `PREFIX_authorized_application`;
CREATE TABLE IF NOT EXISTS `PREFIX_api_client`
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

CREATE TABLE IF NOT EXISTS `PREFIX_mutation` (
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

/* Unify varchar limits */
/* https://github.com/PrestaShop/PrestaShop/pull/35882 */
ALTER TABLE `PREFIX_meta_lang` CHANGE `url_rewrite` `url_rewrite` varchar(255) NOT NULL;
ALTER TABLE `PREFIX_orders` CHANGE `reference` `reference` VARCHAR(255);
ALTER TABLE `PREFIX_tax_rules_group` CHANGE `name` `name` VARCHAR(64) NOT NULL;
ALTER TABLE `PREFIX_mail` CHANGE `recipient` `recipient` varchar(255) NOT NULL;
ALTER TABLE `PREFIX_mail` CHANGE `subject` `subject` varchar(255) NOT NULL;
ALTER TABLE `PREFIX_shop_url` CHANGE `domain` `domain` varchar(255) NOT NULL;
ALTER TABLE `PREFIX_shop_url` CHANGE `domain_ssl` `domain_ssl` varchar(255) NOT NULL;
ALTER TABLE `PREFIX_feature_flag` CHANGE `label_wording` `label_wording` VARCHAR(191) DEFAULT '' NOT NULL;
ALTER TABLE `PREFIX_feature_flag` CHANGE `description_wording` `description_wording` VARCHAR(191) DEFAULT '' NOT NULL;

/* Raise payment reference to unify with orders table */
/* https://github.com/PrestaShop/PrestaShop/pull/37038 */
ALTER TABLE `PREFIX_order_payment` CHANGE `order_reference` `order_reference` VARCHAR(255);

/* Auto generated hooks added for version 9.0.0 */
INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionMailAlterMessageBeforeSend', 'Modify Swift Message before sending', 'This hook is called before the Swift Message is sent in Mail.php', '1'),
  (NULL, 'actionValidateOrderBefore', 'Before validating an order', 'This hook is called before validating an order by core', '1'),
  (NULL, 'actionDuplicateCartData', 'Cart duplication', 'This hook is triggered after all the cart related data has been duplicated', '1'),
  (NULL, 'actionObjectDuplicateAfter', 'After duplicating an object', 'This hook is called after duplicating an object by the core.', '1'),
  (NULL, 'displayCartExtraProductInfo', 'Extra information in shopping cart product line', 'This hook adds extra information to the product lines, in the shopping cart', '1'),
  (NULL, 'actionPresentSupplier', 'Supplier Presenter', 'This hook is called before a supplier is presented', '1'),
  (NULL, 'actionPresentManufacturer', 'Manufacturer Presenter', 'This hook is called before a manufacturer is presented', '1'),
  (NULL, 'actionPresentStore', 'Store Presenter', 'This hook is called before a store is presented', '1'),
  (NULL, 'actionPresentCategory', 'Category Presenter', 'This hook is called before a category is presented', '1'),
  (NULL, 'actionCartGetPackageShippingCost', 'After getting package shipping cost value', 'This hook is called in order to allow to modify package shipping cost', '1'),
  (NULL, 'actionUpdateCartAddress', 'Triggers after changing address on the cart', 'This hook is called after address is changed on the cart', '1'),
  (NULL, 'actionValidateCartRule', 'Alter cart rule validation process', 'Allow modules to implement their own rules to validate a cart rule.', '1'),
  (NULL, 'actionFeatureValueFormBuilderModifier', 'Modify feature value identifiable object form', 'This hook allows to modify feature value identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionCartRuleFormBuilderModifier', 'Modify cart rule identifiable object form', 'This hook allows to modify cart rule identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionTitleFormBuilderModifier', 'Modify title identifiable object form', 'This hook allows to modify title identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionApiClientFormBuilderModifier', 'Modify api client identifiable object form', 'This hook allows to modify api client identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionImageTypeFormBuilderModifier', 'Modify image type identifiable object form', 'This hook allows to modify image type identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionCarrierFormBuilderModifier', 'Modify carrier identifiable object form', 'This hook allows to modify carrier identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionFeatureValueFormDataProviderData', 'Provide feature value identifiable object form data for update', 'This hook allows to provide feature value identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionCartRuleFormDataProviderData', 'Provide cart rule identifiable object form data for update', 'This hook allows to provide cart rule identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionTitleFormDataProviderData', 'Provide title identifiable object form data for update', 'This hook allows to provide title identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionApiClientFormDataProviderData', 'Provide api client identifiable object form data for update', 'This hook allows to provide api client identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionImageTypeFormDataProviderData', 'Provide image type identifiable object form data for update', 'This hook allows to provide image type identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionCarrierFormDataProviderData', 'Provide carrier identifiable object form data for update', 'This hook allows to provide carrier identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionFeatureValueFormDataProviderDefaultData', 'Provide feature value identifiable object default form data for creation', 'This hook allows to provide feature value identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionCartRuleFormDataProviderDefaultData', 'Provide cart rule identifiable object default form data for creation', 'This hook allows to provide cart rule identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionTitleFormDataProviderDefaultData', 'Provide title identifiable object default form data for creation', 'This hook allows to provide title identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionApiClientFormDataProviderDefaultData', 'Provide api client identifiable object default form data for creation', 'This hook allows to provide api client identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionImageTypeFormDataProviderDefaultData', 'Provide image type identifiable object default form data for creation', 'This hook allows to provide image type identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionCarrierFormDataProviderDefaultData', 'Provide carrier identifiable object default form data for creation', 'This hook allows to provide carrier identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionBeforeUpdateFeatureValueFormHandler', 'Modify feature value identifiable object data before updating it', 'This hook allows to modify feature value identifiable object forms data before it was updated', '1'),
  (NULL, 'actionBeforeUpdateCartRuleFormHandler', 'Modify cart rule identifiable object data before updating it', 'This hook allows to modify cart rule identifiable object forms data before it was updated', '1'),
  (NULL, 'actionBeforeUpdateTitleFormHandler', 'Modify title identifiable object data before updating it', 'This hook allows to modify title identifiable object forms data before it was updated', '1'),
  (NULL, 'actionBeforeUpdateApiClientFormHandler', 'Modify api client identifiable object data before updating it', 'This hook allows to modify api client identifiable object forms data before it was updated', '1'),
  (NULL, 'actionBeforeUpdateImageTypeFormHandler', 'Modify image type identifiable object data before updating it', 'This hook allows to modify image type identifiable object forms data before it was updated', '1'),
  (NULL, 'actionBeforeUpdateCarrierFormHandler', 'Modify carrier identifiable object data before updating it', 'This hook allows to modify carrier identifiable object forms data before it was updated', '1'),
  (NULL, 'actionAfterUpdateFeatureValueFormHandler', 'Modify feature value identifiable object data after updating it', 'This hook allows to modify feature value identifiable object forms data after it was updated', '1'),
  (NULL, 'actionAfterUpdateCartRuleFormHandler', 'Modify cart rule identifiable object data after updating it', 'This hook allows to modify cart rule identifiable object forms data after it was updated', '1'),
  (NULL, 'actionAfterUpdateTitleFormHandler', 'Modify title identifiable object data after updating it', 'This hook allows to modify title identifiable object forms data after it was updated', '1'),
  (NULL, 'actionAfterUpdateApiClientFormHandler', 'Modify api client identifiable object data after updating it', 'This hook allows to modify api client identifiable object forms data after it was updated', '1'),
  (NULL, 'actionAfterUpdateImageTypeFormHandler', 'Modify image type identifiable object data after updating it', 'This hook allows to modify image type identifiable object forms data after it was updated', '1'),
  (NULL, 'actionAfterUpdateCarrierFormHandler', 'Modify carrier identifiable object data after updating it', 'This hook allows to modify carrier identifiable object forms data after it was updated', '1'),
  (NULL, 'actionBeforeCreateFeatureValueFormHandler', 'Modify feature value identifiable object data before creating it', 'This hook allows to modify feature value identifiable object forms data before it was created', '1'),
  (NULL, 'actionBeforeCreateCartRuleFormHandler', 'Modify cart rule identifiable object data before creating it', 'This hook allows to modify cart rule identifiable object forms data before it was created', '1'),
  (NULL, 'actionBeforeCreateTitleFormHandler', 'Modify title identifiable object data before creating it', 'This hook allows to modify title identifiable object forms data before it was created', '1'),
  (NULL, 'actionBeforeCreateApiClientFormHandler', 'Modify api client identifiable object data before creating it', 'This hook allows to modify api client identifiable object forms data before it was created', '1'),
  (NULL, 'actionBeforeCreateImageTypeFormHandler', 'Modify image type identifiable object data before creating it', 'This hook allows to modify image type identifiable object forms data before it was created', '1'),
  (NULL, 'actionBeforeCreateCarrierFormHandler', 'Modify carrier identifiable object data before creating it', 'This hook allows to modify carrier identifiable object forms data before it was created', '1'),
  (NULL, 'actionAfterCreateFeatureValueFormHandler', 'Modify feature value identifiable object data after creating it', 'This hook allows to modify feature value identifiable object forms data after it was created', '1'),
  (NULL, 'actionAfterCreateCartRuleFormHandler', 'Modify cart rule identifiable object data after creating it', 'This hook allows to modify cart rule identifiable object forms data after it was created', '1'),
  (NULL, 'actionAfterCreateTitleFormHandler', 'Modify title identifiable object data after creating it', 'This hook allows to modify title identifiable object forms data after it was created', '1'),
  (NULL, 'actionAfterCreateApiClientFormHandler', 'Modify api client identifiable object data after creating it', 'This hook allows to modify api client identifiable object forms data after it was created', '1'),
  (NULL, 'actionAfterCreateImageTypeFormHandler', 'Modify image type identifiable object data after creating it', 'This hook allows to modify image type identifiable object forms data after it was created', '1'),
  (NULL, 'actionAfterCreateCarrierFormHandler', 'Modify carrier identifiable object data after creating it', 'This hook allows to modify carrier identifiable object forms data after it was created', '1'),
  (NULL, 'actionImageSettingsPageForm', 'Modify image settings page options form content', 'This hook allows to modify image settings page options form FormBuilder', '1'),
  (NULL, 'actionAdminAPIForm', 'Modify admin api options form content', 'This hook allows to modify admin api options form FormBuilder', '1'),
  (NULL, 'actionBackOfficeLoginForm', 'Modify back office login options form content', 'This hook allows to modify back office login options form FormBuilder', '1'),
  (NULL, 'actionEmployeeRequestPasswordResetForm', 'Modify employee request password reset options form content', 'This hook allows to modify employee request password reset options form FormBuilder', '1'),
  (NULL, 'actionEmployeeResetPasswordForm', 'Modify employee reset password options form content', 'This hook allows to modify employee reset password options form FormBuilder', '1'),
  (NULL, 'actionImageSettingsPageSave', 'Modify image settings page options form saved data', 'This hook allows to modify data of image settings page options form after it was saved', '1'),
  (NULL, 'actionAdminAPISave', 'Modify admin api options form saved data', 'This hook allows to modify data of admin api options form after it was saved', '1'),
  (NULL, 'actionBackOfficeLoginSave', 'Modify back office login options form saved data', 'This hook allows to modify data of back office login options form after it was saved', '1'),
  (NULL, 'actionEmployeeRequestPasswordResetSave', 'Modify employee request password reset options form saved data', 'This hook allows to modify data of employee request password reset options form after it was saved', '1'),
  (NULL, 'actionEmployeeResetPasswordSave', 'Modify employee reset password options form saved data', 'This hook allows to modify data of employee reset password options form after it was saved', '1'),
  (NULL, 'actionCustomerCartGridDefinitionModifier', 'Modify customer cart grid definition', 'This hook allows to alter customer cart grid columns, actions and filters', '1'),
  (NULL, 'actionCustomerOrderGridDefinitionModifier', 'Modify customer order grid definition', 'This hook allows to alter customer order grid columns, actions and filters', '1'),
  (NULL, 'actionCustomerBoughtProductGridDefinitionModifier', 'Modify customer bought product grid definition', 'This hook allows to alter customer bought product grid columns, actions and filters', '1'),
  (NULL, 'actionCustomerViewedProductGridDefinitionModifier', 'Modify customer viewed product grid definition', 'This hook allows to alter customer viewed product grid columns, actions and filters', '1'),
  (NULL, 'actionCustomerGroupsGridDefinitionModifier', 'Modify customer groups grid definition', 'This hook allows to alter customer groups grid columns, actions and filters', '1'),
  (NULL, 'actionCustomerCartGridQueryBuilderModifier', 'Modify customer cart grid query builder', 'This hook allows to alter Doctrine query builder for customer cart grid', '1'),
  (NULL, 'actionCustomerOrderGridQueryBuilderModifier', 'Modify customer order grid query builder', 'This hook allows to alter Doctrine query builder for customer order grid', '1'),
  (NULL, 'actionCustomerBoughtProductGridQueryBuilderModifier', 'Modify customer bought product grid query builder', 'This hook allows to alter Doctrine query builder for customer bought product grid', '1'),
  (NULL, 'actionCustomerViewedProductGridQueryBuilderModifier', 'Modify customer viewed product grid query builder', 'This hook allows to alter Doctrine query builder for customer viewed product grid', '1'),
  (NULL, 'actionCustomerGroupsGridQueryBuilderModifier', 'Modify customer groups grid query builder', 'This hook allows to alter Doctrine query builder for customer groups grid', '1'),
  (NULL, 'actionCustomerCartGridDataModifier', 'Modify customer cart grid data', 'This hook allows to modify customer cart grid data', '1'),
  (NULL, 'actionCustomerOrderGridDataModifier', 'Modify customer order grid data', 'This hook allows to modify customer order grid data', '1'),
  (NULL, 'actionCustomerBoughtProductGridDataModifier', 'Modify customer bought product grid data', 'This hook allows to modify customer bought product grid data', '1'),
  (NULL, 'actionCustomerViewedProductGridDataModifier', 'Modify customer viewed product grid data', 'This hook allows to modify customer viewed product grid data', '1'),
  (NULL, 'actionCustomerGroupsGridDataModifier', 'Modify customer groups grid data', 'This hook allows to modify customer groups grid data', '1'),
  (NULL, 'actionCustomerCartGridFilterFormModifier', 'Modify customer cart grid filters', 'This hook allows to modify filters for customer cart grid', '1'),
  (NULL, 'actionCustomerOrderGridFilterFormModifier', 'Modify customer order grid filters', 'This hook allows to modify filters for customer order grid', '1'),
  (NULL, 'actionCustomerBoughtProductGridFilterFormModifier', 'Modify customer bought product grid filters', 'This hook allows to modify filters for customer bought product grid', '1'),
  (NULL, 'actionCustomerViewedProductGridFilterFormModifier', 'Modify customer viewed product grid filters', 'This hook allows to modify filters for customer viewed product grid', '1'),
  (NULL, 'actionCustomerGroupsGridFilterFormModifier', 'Modify customer groups grid filters', 'This hook allows to modify filters for customer groups grid', '1'),
  (NULL, 'actionCustomerCartGridPresenterModifier', 'Modify customer cart grid template data', 'This hook allows to modify data which is about to be used in template for customer cart grid', '1'),
  (NULL, 'actionCustomerOrderGridPresenterModifier', 'Modify customer order grid template data', 'This hook allows to modify data which is about to be used in template for customer order grid', '1'),
  (NULL, 'actionCustomerBoughtProductGridPresenterModifier', 'Modify customer bought product grid template data', 'This hook allows to modify data which is about to be used in template for customer bought product grid', '1'),
  (NULL, 'actionCustomerViewedProductGridPresenterModifier', 'Modify customer viewed product grid template data', 'This hook allows to modify data which is about to be used in template for customer viewed product grid', '1'),
  (NULL, 'actionCustomerGroupsGridPresenterModifier', 'Modify customer groups grid template data', 'This hook allows to modify data which is about to be used in template for customer groups grid', '1'),
  (NULL, 'actionPDFInvoiceRender', 'PDF Invoice - Render', 'This hook is called when a PDF invoice is rendered from the Front Office and the Back Office', '1'),
  (NULL, 'actionPresentObject', 'Object Presenter', 'This hook is called before an object is presented', '1'),
  (NULL, 'actionSetInvoice', '', '', '1'),
  (NULL, 'actionOrderHistoryAddAfter', '', '', '1'),
  (NULL, 'actionInvoiceNumberFormatted', '', '', '1'),
  (NULL, 'actionOnImageResizeAfter', '', '', '1'),
  (NULL, 'actionOnImageCutAfter', '', '', '1'),
  (NULL, 'actionSubmitCustomerAddressForm', '', '', '1'),
  (NULL, 'actionCartSummary', '', '', '1'),
  (NULL, 'actionGetExtraMailTemplateVars', '', '', '1'),
  (NULL, 'deleteProductAttribute', '', '', '1'),
  (NULL, 'actionGetProductPropertiesBefore', '', '', '1'),
  (NULL, 'actionGetProductPropertiesAfter', '', '', '1'),
  (NULL, 'displayCustomization', '', '', '1'),
  (NULL, 'actionDeliveryPriceByWeight', '', '', '1'),
  (NULL, 'actionDeliveryPriceByPrice', '', '', '1'),
  (NULL, 'actionDispatcher', '', '', '1'),
  (NULL, 'moduleRoutes', '', '', '1'),
  (NULL, 'actionGetIDZoneByAddressID', '', '', '1'),
  (NULL, 'actionModuleRegisterHookBefore', '', '', '1'),
  (NULL, 'actionModuleRegisterHookAfter', '', '', '1'),
  (NULL, 'actionModuleUnRegisterHookBefore', '', '', '1'),
  (NULL, 'actionModuleUnRegisterHookAfter', '', '', '1'),
  (NULL, 'actionShopDataDuplication', '', '', '1'),
  (NULL, 'actionAdminMetaBeforeWriteRobotsFile', '', '', '1'),
  (NULL, 'actionAdminMetaAfterWriteRobotsFile', '', '', '1'),
  (NULL, 'termsAndConditions', '', '', '1'),
  (NULL, 'actionValidateStepComplete', '', '', '1'),
  (NULL, 'actionAdminControllerSetMedia', '', '', '1'),
  (NULL, 'overrideMinimalPurchasePrice', '', '', '1'),
  (NULL, 'actionFrontControllerSetMedia', '', '', '1'),
  (NULL, 'overrideLayoutTemplate', '', '', '1'),
  (NULL, 'productSearchProvider', '', '', '1'),
  (NULL, 'actionAttributeCombinationDelete', '', '', '1'),
  (NULL, 'actionAttributeCombinationSave', '', '', '1'),
  (NULL, 'actionCustomerBeforeUpdateGroup', '', '', '1'),
  (NULL, 'actionCustomerAddGroups', '', '', '1'),
  (NULL, 'actionProductCoverage', '', '', '1'),
  (NULL, 'actionObjectAddBefore', '', '', '1'),
  (NULL, 'actionObjectAddAfter', '', '', '1'),
  (NULL, 'actionObjectUpdateBefore', '', '', '1'),
  (NULL, 'actionObjectUpdateAfter', '', '', '1'),
  (NULL, 'actionObjectDeleteBefore', '', '', '1'),
  (NULL, 'actionObjectDeleteAfter', '', '', '1'),
  (NULL, 'actionWishlistAddProduct', '', '', '1'),
  (NULL, 'displayGDPRConsent', '', '', '1'),
  (NULL, 'actionObjectProductCommentValidateAfter', '', '', '1'),
  (NULL, 'actionExportGDPRData', '', '', '1'),
  (NULL, 'actionDeleteGDPRCustomer', '', '', '1'),
  (NULL, 'actionModuleMailAlertSendCustomer', '', '', '1'),
  (NULL, 'actionNewsletterRegistrationBefore', '', '', '1'),
  (NULL, 'actionNewsletterRegistrationAfter', '', '', '1'),
  (NULL, 'displayNewsletterRegistration', '', '', '1'),
  (NULL, 'dashboardZoneOne', '', '', '1'),
  (NULL, 'dashboardZoneTwo', '', '', '1'),
  (NULL, 'dashboardData', '', '', '1'),
  (NULL, 'actionPasswordRenew', '', '', '1'),
  (NULL, 'actionDownloadAttachment', '', '', '1'),
  (NULL, 'displayReassurance', '', '', '1'),
  (NULL, 'displayProductPriceBlock', '', '', '1'),
  (NULL, 'displayProductListReviews', '', '', '1'),
  (NULL, 'displayCrossSellingShoppingCart', '', '', '1'),
  (NULL, 'displayExpressCheckout', '', '', '1'),
  (NULL, 'displayCheckoutSubtotalDetails', '', '', '1'),
  (NULL, 'displayNav1', '', '', '1'),
  (NULL, 'displayNav2', '', '', '1'),
  (NULL, 'displayOrderConfirmation1', '', '', '1'),
  (NULL, 'displayOrderConfirmation2', '', '', '1'),
  (NULL, 'displayFooterBefore', '', '', '1'),
  (NULL, 'displayFooterAfter', '', '', '1'),
  (NULL, 'displayCMSDisputeInformation', '', '', '1'),
  (NULL, 'displayCMSPrintButton', '', '', '1'),
  (NULL, 'displaySearch', '', '', '1'),
  (NULL, 'displayNotFound', '', '', '1'),
  (NULL, 'displayAdminAfterHeader', '', '', '1'),
  (NULL, 'displayAdminNavBarBeforeEnd', '', '', '1'),
  (NULL, 'displayAdminListBefore', '', '', '1'),
  (NULL, 'displayAdminListAfter', '', '', '1'),
  (NULL, 'displayAdminOptions', '', '', '1'),
  (NULL, 'displayAdminForm', '', '', '1'),
  (NULL, 'displayAdminView', '', '', '1'),
  (NULL, 'displayAdminOrderSideBottom', '', '', '1'),
  (NULL, 'displayOrderPreview', '', '', '1'),
  (NULL, 'displayAdminLogin', '', '', '1'),
  (NULL, 'actionPresentModule', '', '', '1'),
  (NULL, 'actionAdminThemesControllerUpdate_optionsAfter', '', '', '1'),
  (NULL, 'actionAdminDuplicateBefore', '', '', '1'),
  (NULL, 'actionAdminDuplicateAfter', '', '', '1'),
  (NULL, 'actionSearch', '', '', '1'),
  (NULL, 'actionSearchTermFormBuilderModifier', 'Modify search term identifiable object form', 'This hook allows to modify search term identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionSearchTermFormDataProviderData', 'Provide search term identifiable object form data for update', 'This hook allows to provide search term identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionSearchTermFormDataProviderDefaultData', 'Provide search term identifiable object default form data for creation', 'This hook allows to provide search term identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionBeforeUpdateSearchTermFormHandler', 'Modify search term identifiable object data before updating it', 'This hook allows to modify search term identifiable object forms data before it was updated', '1'),
  (NULL, 'actionAfterUpdateSearchTermFormHandler', 'Modify search term identifiable object data after updating it', 'This hook allows to modify search term identifiable object forms data after it was updated', '1'),
  (NULL, 'actionBeforeCreateSearchTermFormHandler', 'Modify search term identifiable object data before creating it', 'This hook allows to modify search term identifiable object forms data before it was created', '1'),
  (NULL, 'actionAfterCreateSearchTermFormHandler', 'Modify search term identifiable object data after creating it', 'This hook allows to modify search term identifiable object forms data after it was created', '1'),
  (NULL, 'actionProductGetAttributesGroupsBefore', 'Triggers before getting product attributes groups', 'Allows to modify product attributes groups SQL query before they are retrieved from the database.', '1'),
  (NULL, 'actionProductGetAttributesGroupsAfter', 'Triggers after getting product attributes groups', 'Allows to modify product attributes groups after they are retrieved from the database.', '1'),
  (NULL, 'actionGetPdfRenderer', 'Provide a PDF renderer', 'This hook allows to provide a custom PDF renderer to generate PDF files', '1'),
  (NULL, 'displayAdminStoreInformation', 'Display extra store information', 'This hook displays content in the Information page to add store information', '1'),
  -- https://github.com/PrestaShop/PrestaShop/pull/34133
  (NULL, 'actionSubmitAccountBefore', 'Before customer account creation', 'This hook is called before a customer account creation', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

/* Auto generated hooks removed for version 9.0.0 */
DELETE FROM `PREFIX_hook` WHERE `name` IN (
  'actionAdminLoginControllerBefore',
  'actionAdminLoginControllerLoginBefore',
  'actionAdminLoginControllerLoginAfter',
  'actionAdminLoginControllerForgotBefore',
  'actionAdminLoginControllerForgotAfter',
  'actionAdminLoginControllerResetBefore',
  'actionAdminLoginControllerResetAfter',
  'actionBeforeEnableMobileModule',
  'actionBeforeDisableMobileModule',
  'actionAjaxDieBefore'
);
/* Clean hook registrations related to removed hooks */
DELETE FROM `PREFIX_hook_module` WHERE `id_hook` NOT IN (SELECT id_hook FROM `PREFIX_hook`);
DELETE FROM `PREFIX_hook_module_exceptions` WHERE `id_hook` NOT IN (SELECT id_hook FROM `PREFIX_hook`);

/* Feature value position */
/* https://github.com/PrestaShop/PrestaShop/pull/37042 */
/* PHP:add_column('feature_value', 'position', 'int(10) unsigned NOT NULL DEFAULT \'0\''); */;
/* PHP:add_configuration_if_not_exists('PS_FEATURE_VALUES_ORDER', 'name'); */;

/* Upgrade attachment names length */
/* https://github.com/PrestaShop/PrestaShop/pull/37598 */
ALTER TABLE `PREFIX_attachment` MODIFY COLUMN `file_name` varchar(255) NOT NULL;
ALTER TABLE `PREFIX_attachment_lang` MODIFY COLUMN `name` varchar(255) DEFAULT NULL;

/* Fix category thumbnail images */
/* https://github.com/PrestaShop/PrestaShop/pull/36877 */
/* PHP:ps_900_migrate_category_images(); */;

/* Add id_product in customer message table */
/* https://github.com/PrestaShop/PrestaShop/pull/37861 */
/* PHP:add_column('customer_message', 'id_product', 'INT UNSIGNED DEFAULT NULL AFTER `id_employee`'); */;
ALTER TABLE `PREFIX_customer_message` ADD INDEX `id_product` (`id_product`);

/* Update Admin API tabs and roles */
UPDATE `PREFIX_tab` SET `wording`='Admin API', `wording_domain`='Admin.Navigation.Menu', `class_name`='AdminAdminAPI', `route_name`='admin_api_index', `active`=1 WHERE `class_name`='AdminAuthorizationServer';
/* PHP:ps_update_tab_lang('Admin.Navigation.Menu', 'AdminAdminAPI'); */;

UPDATE `PREFIX_authorization_role` SET `slug`='ROLE_MOD_TAB_ADMINADMINAPI_CREATE' WHERE `slug`='ROLE_MOD_TAB_ADMINAUTHORIZATIONSERVER_CREATE';
UPDATE `PREFIX_authorization_role` SET `slug`='ROLE_MOD_TAB_ADMINADMINAPI_READ' WHERE `slug`='ROLE_MOD_TAB_ADMINAUTHORIZATIONSERVER_READ';
UPDATE `PREFIX_authorization_role` SET `slug`='ROLE_MOD_TAB_ADMINADMINAPI_UPDATE' WHERE `slug`='ROLE_MOD_TAB_ADMINAUTHORIZATIONSERVER_UPDATE';
UPDATE `PREFIX_authorization_role` SET `slug`='ROLE_MOD_TAB_ADMINADMINAPI_DELETE' WHERE `slug`='ROLE_MOD_TAB_ADMINAUTHORIZATIONSERVER_DELETE';
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
    ('PS_ENABLE_ADMIN_API', '1', NOW(), NOW()),
    ('PS_ADMIN_API_FORCE_DEBUG_SECURED', '1', NOW(), NOW())
;
/* PHP:install_ps_apiresources(); */;

/* Reorganize search aliases */
/* https://github.com/PrestaShop/PrestaShop/pull/37470 */
/* PHP:ps_900_reorganize_aliases_tab(); */;

/* Add theme_name in image type table */
/* https://github.com/PrestaShop/PrestaShop/pull/38745 */
/* https://github.com/PrestaShop/PrestaShop/pull/38767 */
ALTER TABLE `PREFIX_image_type`
    ADD COLUMN `theme_name` VARCHAR(255) DEFAULT NULL AFTER `stores`,
    ADD UNIQUE KEY `UNIQ_907C95215E237E0614E48A3B` (`name`,`theme_name`),
    DROP INDEX `UNIQ_907C95215E237E06`;
