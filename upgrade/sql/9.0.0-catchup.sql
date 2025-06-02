/* script intended for catching up with requests forgotten since 1.7 */

/* 1.7.2.0 */
/* PHP:add_configuration_if_not_exists('PS_PRODUCT_SHORT_DESC_LIMIT', '800'); */;

/* 1.7.3.0 */
UPDATE `PREFIX_configuration` SET `value` = 'In Stock' WHERE `name` = "PS_LABEL_IN_STOCK_PRODUCTS" AND `value` IS NULL;
UPDATE `PREFIX_configuration` SET `value` = 'Product available for orders' WHERE `name` = "PS_LABEL_OOS_PRODUCTS_BOA" AND `value` IS NULL;
UPDATE `PREFIX_configuration` SET `value` = 'Out-of-Stock' WHERE `name` = "PS_LABEL_OOS_PRODUCTS_BOD" AND `value` IS NULL;
UPDATE `PREFIX_configuration` SET `value` = '28' WHERE `name` = "SHOP_LOGO_HEIGHT" AND `value` = '23';
UPDATE `PREFIX_configuration` SET `value` = '100' WHERE `name` = "SHOP_LOGO_WIDTH" AND `value` = '117';

/* 1.7.4.0 */
INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES
  ('actionBuildFrontEndObject', 'Manage elements added to the \"prestashop\" javascript object', 'This hook allows you to customize the \"prestashop\" javascript object that is included in all front office pages', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

/* 1.7.5.0 */
INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES
  ('actionBackupGridDefinitionModifier', 'Modifying DB Backup grid definition', 'This hook allows to alter DB Backup grid columns, actions and filters', 1),
  ('actionBackupGridFilterFormModifier', 'Modify filters form for DB Backup grid', 'This hook allows to alter filters form used in DB Backup', 1),
  ('actionBackupGridPresenterModifier', 'Modify DB Backup grid view data', 'This hook allows to alter presented DB Backup grid data', 1),
  ('actionEmailLogsGridDefinitionModifier', 'Modifying E-mail grid definition', 'This hook allows to alter E-mail grid columns, actions and filters', 1),
  ('actionEmailLogsGridFilterFormModifier', 'Modify filters form for E-mail grid', 'This hook allows to alter filters form used in E-mail', 1),
  ('actionEmailLogsGridPresenterModifier', 'Modify E-mail grid view data', 'This hook allows to alter presented E-mail grid data', 1),
  ('actionEmailLogsGridQueryBuilderModifier', 'Modify E-mail grid query builder', 'This hook allows to alter Doctrine query builder for E-mail grid', 1),
  ('actionLogsGridDefinitionModifier', 'Modifying Logs grid definition', 'This hook allows to alter Logs grid columns, actions and filters', 1),
  ('actionLogsGridFilterFormModifier', 'Modify filters form for Logs grid', 'This hook allows to alter filters form used in Logs', 1),
  ('actionLogsGridPresenterModifier', 'Modify Logs grid view data', 'This hook allows to alter presented Logs grid data', 1),
  ('actionLogsGridQueryBuilderModifier', 'Modify Logs grid query builder', 'This hook allows to alter Doctrine query builder for Logs grid', 1),
  ('actionMetaGridDefinitionModifier', 'Modifying SEO and URLs grid definition', 'This hook allows to alter SEO and URLs grid columns, actions and filters', 1),
  ('actionMetaGridFilterFormModifier', 'Modify filters form for SEO and URLs grid', 'This hook allows to alter filters form used in SEO and URLs', 1),
  ('actionMetaGridPresenterModifier', 'Modify SEO and URLs grid view data', 'This hook allows to alter presented SEO and URLs grid data', 1),
  ('actionMetaGridQueryBuilderModifier', 'Modify SEO and URLs grid query builder', 'This hook allows to alter Doctrine query builder for SEO and URLs grid', 1),
  ('actionSqlRequestGridDefinitionModifier', 'Modifying SQL Manager grid definition', 'This hook allows to alter SQL Manager grid columns, actions and filters', 1),
  ('actionSqlRequestGridFilterFormModifier', 'Modify filters form for SQL Manager grid', 'This hook allows to alter filters form used in SQL Manager', 1),
  ('actionSqlRequestGridPresenterModifier', 'Modify SQL Manager grid view data', 'This hook allows to alter presented SQL Manager grid data', 1),
  ('actionSqlRequestGridQueryBuilderModifier', 'Modify SQL Manager grid query builder', 'This hook allows to alter Doctrine query builder for SQL Manager grid', 1),
  ('actionWebserviceKeyGridDefinitionModifier', 'Modifying Webservice grid definition', 'This hook allows to alter Webservice grid columns, actions and filters', 1),
  ('actionWebserviceKeyGridFilterFormModifier', 'Modify filters form for Webservice grid', 'This hook allows to alter filters form used in Webservice', 1),
  ('actionWebserviceKeyGridPresenterModifier', 'Modify Webservice grid view data', 'This hook allows to alter presented Webservice grid data', 1),
  ('actionWebserviceKeyGridQueryBuilderModifier', 'Modify Webservice grid query builder', 'This hook allows to alter Doctrine query builder for Webservice grid', 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

/* 1.7.6.0 */
INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES
	('additionalCustomerAddressFields', 'Add fields to the Customer address form', 'This hook returns an array of FormFields to add them to the customer address registration form', '1'),
  ('displayPersonalInformationTop', 'Content in the checkout funnel, on top of the personal information panel', 'Display actions or additional content in the personal details tab of the checkout funnel.', '1'),
  ('displayProductActions', 'Display additional action button on the product page', 'This hook allow additional actions to be triggered, near the add to cart button.', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

/* 1.7.7.0 */
INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES
  ('actionAfterCreateFeatureFormHandler', 'Modify feature identifiable object data after creating it','This hook allows to modify feature identifiable object forms data after it was created', '1'),
  ('actionAfterUpdateFeatureFormHandler', 'Modify feature identifiable object data after updating it','This hook allows to modify feature identifiable object forms data after it was updated', '1'),
  ('displayAdminOrderBottom', 'Admin Order Side Column Bottom','This hook displays content in the order view page at the bottom of the side column', '1'),
  ('actionFeatureFormBuilderModifier', 'Modify feature identifiable object form', 'This hook allows to modify feature identifiable object forms content by modifying form builder data or FormBuilder itself', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

UPDATE `PREFIX_hook_module` AS hm
INNER JOIN `PREFIX_hook` AS hfrom ON hm.id_hook = hfrom.id_hook AND hfrom.name = 'displayAdminOrderSideBottom'
INNER JOIN `PREFIX_hook` AS hto ON hto.name = 'displayAdminOrderBottom'
SET hm.id_hook = hto.id_hook;

UPDATE `PREFIX_country` SET `zip_code_format` = 'NNNNN' WHERE `iso_code` = "KR";

/* PHP:ps_1770_add_states(); */;

/* 1.7.8.0 */
INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES
  ('actionAfterCreateZoneFormHandler','Modify zone identifiable object data after creating it','This hook allows to modify zone identifiable object forms data after it was created','1'),
  ('actionAdminAdministrationControllerPostProcessBefore','On post-process in Admin Configure Advanced Parameters Administration Controller','This hook is called on Admin Configure Advanced Parameters Administration post-process before processing any form','1'),
  ('actionAdminAdministrationControllerPostProcessGeneralBefore','On post-process in Admin Configure Advanced Parameters Administration Controller','This hook is called on Admin Configure Advanced Parameters Administration post-process before processing the General form','1'),
  ('actionAdminAdministrationControllerPostProcessNotificationsBefore','On post-process in Admin Configure Advanced Parameters Administration Controller','This hook is called on Admin Configure Advanced Parameters Administration post-process before processing the Notifications form','1'),
  ('actionAdminAdministrationControllerPostProcessUploadQuotaBefore','On post-process in Admin Configure Advanced Parameters Administration Controller','This hook is called on Admin Configure Advanced Parameters Administration post-process before processing the Upload Quota form','1'),
  ('actionAdminAdminShopParametersMetaControllerPostProcessBefore','On post-process in Admin Configure Shop Parameters Meta Controller','This hook is called on Admin Configure Shop Parameters Meta post-process before processing any form','1'),
  ('actionAdminAdvancedParametersPerformanceControllerPostProcessBefore','On post-process in Admin Configure Advanced Parameters Performance Controller','This hook is called on Admin Configure Advanced Parameters Performance post-process before processing any form','1'),
  ('actionAdminAdvancedParametersPerformanceControllerPostProcessCachingBefore','On post-process in Admin Configure Advanced Parameters Performance Controller','This hook is called on Admin Configure Advanced Parameters Performance post-process before processing the Caching form','1'),
  ('actionAdminAdvancedParametersPerformanceControllerPostProcessCombineCompressCacheBefore','On post-process in Admin Configure Advanced Parameters Performance Controller','This hook is called on Admin Configure Advanced Parameters Performance post-process before processing the Combine Compress Cache form','1'),
  ('actionAdminAdvancedParametersPerformanceControllerPostProcessDebugModeBefore','On post-process in Admin Configure Advanced Parameters Performance Controller','This hook is called on Admin Configure Advanced Parameters Performance post-process before processing the Debug Mode form','1'),
  ('actionAdminAdvancedParametersPerformanceControllerPostProcessMediaServersBefore','On post-process in Admin Configure Advanced Parameters Performance Controller','This hook is called on Admin Configure Advanced Parameters Performance post-process before processing the Media Servers form','1'),
  ('actionAdminAdvancedParametersPerformanceControllerPostProcessOptionalFeaturesBefore','On post-process in Admin Configure Advanced Parameters Performance Controller','This hook is called on Admin Configure Advanced Parameters Performance post-process before processing the Optional Features form','1'),
  ('actionAdminAdvancedParametersPerformanceControllerPostProcessSmartyBefore','On post-process in Admin Configure Advanced Parameters Performance Controller','This hook is called on Admin Configure Advanced Parameters Performance post-process before processing the Smarty form','1'),
  ('actionAdminInternationalGeolocationControllerPostProcessBefore','On post-process in Admin Improve International Geolocation Controller','This hook is called on Admin Improve International Geolocation post-process before processing any form','1'),
  ('actionAdminInternationalGeolocationControllerPostProcessByIpAddressBefore','On post-process in Admin Improve International Geolocation Controller','This hook is called on Admin Improve International Geolocation post-process before processing the By Ip Address form','1'),
  ('actionAdminInternationalGeolocationControllerPostProcessOptionsBefore','On post-process in Admin Improve International Geolocation Controller','This hook is called on Admin Improve International Geolocation post-process before processing the Options form','1'),
  ('actionAdminInternationalGeolocationControllerPostProcessWhitelistBefore','On post-process in Admin Improve International Geolocation Controller','This hook is called on Admin Improve International Geolocation post-process before processing the Whitelist form','1'),
  ('actionAdminInternationalLocalizationControllerPostProcessAdvancedBefore','On post-process in Admin Improve International Localization Controller','This hook is called on Admin Improve International Localization post-process before processing the Advanced form','1'),
  ('actionAdminInternationalLocalizationControllerPostProcessBefore','On post-process in Admin Improve International Localization Controller','This hook is called on Admin Improve International Localization post-process before processing any form','1'),
  ('actionAdminInternationalLocalizationControllerPostProcessConfigurationBefore','On post-process in Admin Improve International Localization Controller','This hook is called on Admin Improve International Localization post-process before processing the Configuration form','1'),
  ('actionAdminInternationalLocalizationControllerPostProcessLocalUnitsBefore','On post-process in Admin Improve International Localization Controller','This hook is called on Admin Improve International Localization post-process before processing the Local Units form','1'),
  ('actionAdminShippingPreferencesControllerPostProcessBefore','On post-process in Admin Improve Shipping Preferences Controller','This hook is called on Admin Improve Shipping Preferences post-process before processing any form','1'),
  ('actionAdminShippingPreferencesControllerPostProcessCarrierOptionsBefore','On post-process in Admin Improve Shipping Preferences Controller','This hook is called on Admin Improve Shipping Preferences post-process before processing the Carrier Options form','1'),
  ('actionAdminShippingPreferencesControllerPostProcessHandlingBefore','On post-process in Admin Improve Shipping Preferences Controller','This hook is called on Admin Improve Shipping Preferences post-process before processing the Handling form','1'),
  ('actionAdminShopParametersMetaControllerPostProcessSeoOptionsBefore','On post-process in Admin Configure Shop Parameters Meta Controller','This hook is called on Admin Configure Shop Parameters Meta post-process before processing the Seo Options form','1'),
  ('actionAdminShopParametersMetaControllerPostProcessSetUpUrlsBefore','On post-process in Admin Configure Shop Parameters Meta Controller','This hook is called on Admin Configure Shop Parameters Meta post-process before processing the SetUp Urls form','1'),
  ('actionAdminShopParametersMetaControllerPostProcessShopUrlsBefore','On post-process in Admin Configure Shop Parameters Meta Controller','This hook is called on Admin Configure Shop Parameters Meta post-process before processing the Shop Urls form','1'),
  ('actionAdminShopParametersMetaControllerPostProcessUrlSchemaBefore','On post-process in Admin Configure Shop Parameters Meta Controller','This hook is called on Admin Configure Shop Parameters Meta post-process before processing the Url Schema form','1'),
  ('actionAdminShopParametersOrderPreferencesControllerPostProcessBefore','On post-process in Admin Configure Shop Parameters Order Preferences Controller','This hook is called on Admin Configure Shop Parameters Order Preferences post-process before processing any form','1'),
  ('actionAdminShopParametersOrderPreferencesControllerPostProcessGeneralBefore','On post-process in Admin Configure Shop Parameters Order Preferences Controller','This hook is called on Admin Configure Shop Parameters Order Preferences post-process before processing the General form','1'),
  ('actionAdminShopParametersOrderPreferencesControllerPostProcessGiftOptionsBefore','On post-process in Admin Configure Shop Parameters Order Preferences Controller','This hook is called on Admin Configure Shop Parameters Order Preferences post-process before processing the Gift Options form','1'),
  ('displayAfterTitleTag','After title tag','Use this hook to add content after title tag','1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

/* 8.0.0 */
INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES
	('actionAfterCreateCartSummaryFormHandler','Modify back office order data after creating it','This hook allows to modify order created from back office data after it is created', '1'),
  ('actionBeforeCreateCartSummaryFormHandler','Modify back office order data before creating it','This hook allows to modify order created from back office data before it is created', '1'),
  ('actionBeforeDisableModule','Before a module is disabled','This hook is called just before a module is disabled', '1'),
  ('actionBeforeEnableModule','Before a module is enabled','This hook is called just before a module is enabled', '1'),
  ('actionBeforeInstallModule','Before a module is installed','This hook is called just before a module is installed', '1'),
  ('actionBeforePostInstallModule','Before method `postInstall()` is called','This hook is called juste before a module execute its `postInstall()` method', '1'),
  ('actionBeforeResetModule','Before a module is reset','This hook is called just before a module is reset', '1'),
  ('actionBeforeUninstallModule','Before a module is uninstalled','This hook is called just before a module is uninstalled', '1'),
  ('actionBeforeUpgradeModule','Before a module is upgraded','This hook is called just before a module is upgraded', '1'),
  ('actionFilterDeliveryOptionList','Modify delivery option list result','This hook allows you to modify delivery option list', '1'),
  ('actionGetAdminToolbarButtons','Allows to add buttons in any toolbar in the back office','This hook allows you to define descriptions of buttons to add in any toolbar of the back office', '1'),
  ('actionListModules','Add modules to the module manager list','This hook allows you to add modules to the list of modules displayed in the module manager page', '1'),
  ('actionValidateOrderAfter','After validating an order','This hook is called after validating an order by core', '1'),
  ('displayBackOfficeEmployeeMenu','Administration Employee menu','This hook is displayed in the employee menu', '1'),
  ('displayEmptyModuleCategoryExtraMessage', 'Extra message to display for an empty modules category', "This hook allows to add an extra message to display in the Module manager page when a category doesn't have any module", '1'),
  ('actionStateGridPresenterModifier','Modify state grid template data','This hook allows to modify data which is about to be used in template for state grid', '1'),
  ('actionTitleGridPresenterModifier','Modify title grid template data','This hook allows to modify data which is about to be used in template for title grid', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

UPDATE `PREFIX_state` SET `iso_code` = 'AGU' WHERE `name` = 'Aguascalientes';

/* 8.1.0 */
INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES
	('actionProductPriceCalculation', 'Product Price Calculation', 'This hook is called into the priceCalculation method to be able to override the price calculation', '1'),
  ('actionAfterCreateCountryFormHandler','Modify country identifiable object data after creating it','This hook allows to modify country identifiable object forms data after it was created', '1'),
  ('actionAfterCreateOrderReturnFormHandler','Modify order return identifiable object data after creating it','This hook allows to modify order return identifiable object forms data after it was created', '1'),
  ('actionAfterCreateProductShopsFormHandler','Modify product shops identifiable object data after creating it','This hook allows to modify product shops identifiable object forms data after it was created', '1'),
  ('actionAfterCreateStateFormHandler','Modify state identifiable object data after creating it','This hook allows to modify state identifiable object forms data after it was created', '1'),
  ('actionAfterCreateTaxRulesGroupFormHandler','Modify tax rules group identifiable object data after creating it','This hook allows to modify tax rules group identifiable object forms data after it was created', '1'),
  ('actionAfterUpdateCountryFormHandler','Modify country identifiable object data after updating it','This hook allows to modify country identifiable object forms data after it was updated', '1'),
  ('actionAfterUpdateOrderReturnFormHandler','Modify order return identifiable object data after updating it','This hook allows to modify order return identifiable object forms data after it was updated', '1'),
  ('actionAfterUpdateProductShopsFormHandler','Modify product shops identifiable object data after updating it','This hook allows to modify product shops identifiable object forms data after it was updated', '1'),
  ('actionAfterUpdateStateFormHandler','Modify state identifiable object data after updating it','This hook allows to modify state identifiable object forms data after it was updated', '1'),
  ('actionAfterUpdateTaxRulesGroupFormHandler','Modify tax rules group identifiable object data after updating it','This hook allows to modify tax rules group identifiable object forms data after it was updated', '1'),
  ('actionBeforeCreateCountryFormHandler','Modify country identifiable object data before creating it','This hook allows to modify country identifiable object forms data before it was created', '1'),
  ('actionBeforeCreateOrderReturnFormHandler','Modify order return identifiable object data before creating it','This hook allows to modify order return identifiable object forms data before it was created', '1'),
  ('actionBeforeCreateProductShopsFormHandler','Modify product shops identifiable object data before creating it','This hook allows to modify product shops identifiable object forms data before it was created', '1'),
  ('actionBeforeCreateStateFormHandler','Modify state identifiable object data before creating it','This hook allows to modify state identifiable object forms data before it was created', '1'),
  ('actionBeforeCreateTaxRulesGroupFormHandler','Modify tax rules group identifiable object data before creating it','This hook allows to modify tax rules group identifiable object forms data before it was created', '1'),
  ('actionBeforeUpdateCountryFormHandler','Modify country identifiable object data before updating it','This hook allows to modify country identifiable object forms data before it was updated', '1'),
  ('actionBeforeUpdateOrderReturnFormHandler','Modify order return identifiable object data before updating it','This hook allows to modify order return identifiable object forms data before it was updated', '1'),
  ('actionBeforeUpdateProductShopsFormHandler','Modify product shops identifiable object data before updating it','This hook allows to modify product shops identifiable object forms data before it was updated', '1'),
  ('actionBeforeUpdateStateFormHandler','Modify state identifiable object data before updating it','This hook allows to modify state identifiable object forms data before it was updated', '1'),
  ('actionBeforeUpdateTaxRulesGroupFormHandler','Modify tax rules group identifiable object data before updating it','This hook allows to modify tax rules group identifiable object forms data before it was updated', '1'),
  ('actionCountryFormBuilderModifier','Modify country identifiable object form','This hook allows to modify country identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  ('actionCountryFormDataProviderData','Provide country identifiable object form data for update','This hook allows to provide country identifiable object form data which will prefill the form in update/edition page', '1'),
  ('actionCountryFormDataProviderDefaultData','Provide country identifiable object default form data for creation','This hook allows to provide country identifiable object form data which will prefill the form in creation page', '1'),
  ('actionCustomerThreadGridDataModifier','Modify customer thread grid data','This hook allows to modify customer thread grid data', '1'),
  ('actionCustomerThreadGridDefinitionModifier','Modify customer thread grid definition','This hook allows to alter customer thread grid columns, actions and filters', '1'),
  ('actionCustomerThreadGridFilterFormModifier','Modify customer thread grid filters','This hook allows to modify filters for customer thread grid', '1'),
  ('actionCustomerThreadGridPresenterModifier','Modify customer thread grid template data','This hook allows to modify data which is about to be used in template for customer thread grid', '1'),
  ('actionCustomerThreadGridQueryBuilderModifier','Modify customer thread grid query builder','This hook allows to alter Doctrine query builder for customer thread grid', '1'),
  ('actionOrderReturnFormBuilderModifier','Modify order return identifiable object form','This hook allows to modify order return identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  ('actionOrderReturnFormDataProviderData','Provide order return identifiable object form data for update','This hook allows to provide order return identifiable object form data which will prefill the form in update/edition page', '1'),
  ('actionOrderReturnFormDataProviderDefaultData','Provide order return identifiable object default form data for creation','This hook allows to provide order return identifiable object form data which will prefill the form in creation page', '1'),
  ('actionProductShopsFormBuilderModifier','Modify product shops identifiable object form','This hook allows to modify product shops identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  ('actionProductShopsFormDataProviderData','Provide product shops identifiable object form data for update','This hook allows to provide product shops identifiable object form data which will prefill the form in update/edition page', '1'),
  ('actionProductShopsFormDataProviderDefaultData','Provide product shops identifiable object default form data for creation','This hook allows to provide product shops identifiable object form data which will prefill the form in creation page', '1'),
  ('actionStateFormBuilderModifier','Modify state identifiable object form','This hook allows to modify state identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  ('actionStateFormDataProviderData','Provide state identifiable object form data for update','This hook allows to provide state identifiable object form data which will prefill the form in update/edition page', '1'),
  ('actionStateFormDataProviderDefaultData','Provide state identifiable object default form data for creation','This hook allows to provide state identifiable object form data which will prefill the form in creation page', '1'),
  ('actionTaxRulesGroupFormBuilderModifier','Modify tax rules group identifiable object form','This hook allows to modify tax rules group identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  ('actionTaxRulesGroupFormDataProviderData','Provide tax rules group identifiable object form data for update','This hook allows to provide tax rules group identifiable object form data which will prefill the form in update/edition page', '1'),
  ('actionTaxRulesGroupFormDataProviderDefaultData','Provide tax rules group identifiable object default form data for creation','This hook allows to provide tax rules group identifiable object form data which will prefill the form in creation page', '1'),
  ('displayContactContent','Content wrapper section of the contact page','This hook displays new elements in the content wrapper of the contact page', '1'),
  ('displayContactLeftColumn','Left column blocks on the contact page','This hook displays new elements in the left-hand column of the contact page', '1'),
  ('displayContactRightColumn','Right column blocks of the contact page','This hook displays new elements in the right-hand column of the contact page', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);


INSERT IGNORE INTO `PREFIX_authorization_role` (`slug`) VALUES
  ('ROLE_MOD_TAB_ADMINADMINAPI_CREATE'),
  ('ROLE_MOD_TAB_ADMINADMINAPI_DELETE'),
  ('ROLE_MOD_TAB_ADMINADMINAPI_READ'),
  ('ROLE_MOD_TAB_ADMINADMINAPI_UPDATE');

INSERT IGNORE INTO `PREFIX_feature_flag` (`name`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`)
VALUES
    ('cart_rule', 'Cart rules', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated cart rules page.', 'Admin.Advparameters.Help', 0, 'beta'),
    ('catalog_price_rule', 'Catalog price rules', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated catalog price rules page.', 'Admin.Advparameters.Help', 0, 'beta'),
    ('country', 'Countries', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated countries page.', 'Admin.Advparameters.Help', 0, 'beta'),
    ('state', 'States', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated states page.', 'Admin.Advparameters.Help', 0, 'beta'),
    ('carrier', 'Carriers', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated carriers page.', 'Admin.Advparameters.Help', 0, 'stable'),
    ('permission', 'Permissions', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated permissions page.', 'Admin.Advparameters.Help', 0, 'beta'),
    ('tax_rules_group', 'Tax rule groups', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated tax rules page.', 'Admin.Advparameters.Help', 0, 'beta'),
    ('customer_threads', 'Customer threads', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated customer threads page.', 'Admin.Advparameters.Help', 0, 'beta');

/* 8.2.0 */
INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES
  ('actionFrontControllerSetVariablesBefore','Add general purpose variables in JavaScript object and Smarty templates before assignation.','Allows defining variables for the JavaScript object before the core does it.', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);
