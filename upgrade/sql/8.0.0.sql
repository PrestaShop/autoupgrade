SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

DROP TABLE IF EXISTS `PREFIX_referrer`;
DROP TABLE IF EXISTS `PREFIX_referrer_cache`;
DROP TABLE IF EXISTS `PREFIX_referrer_shop`;
DROP TABLE IF EXISTS `PREFIX_attribute_impact`;

/* Remove page Referrers */
## Remove Tabs
/* PHP:ps_remove_controller_tab('AdminThemesCatalog'); */;
/* PHP:ps_remove_controller_tab('AdminParentModulesCatalog'); */;
/* PHP:ps_remove_controller_tab('AdminModulesCatalog'); */;
/* PHP:ps_remove_controller_tab('AdminAddonsCatalog'); */;
/* PHP:ps_remove_controller_tab('AdminReferrers'); */;
## Remove Roles
/* For SalesMan profile, remove parent tab `Traffic & SEO` */
DELETE FROM `PREFIX_access`
  WHERE `id_authorization_role` IN (SELECT `id_authorization_role` FROM `PREFIX_authorization_role` WHERE `slug` LIKE 'ROLE_MOD_TAB_ADMINPARENTMETA_%')
  AND `id_profile` = 4;
## Remove Configuration
DELETE FROM `PREFIX_configuration`
  WHERE `name` IN ('PS_REFERRERS_CACHE_LIKE', 'PS_REFERRERS_CACHE_DATE');

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
    ('PS_MAIL_DKIM_ENABLE', '0', NOW(), NOW()),
    ('PS_MAIL_DKIM_DOMAIN', '', NOW(), NOW()),
    ('PS_MAIL_DKIM_SELECTOR', '', NOW(), NOW()),
    ('PS_MAIL_DKIM_KEY', '', NOW(), NOW()),
    ('PS_WEBP_QUALITY', '80', NOW(), NOW()),
    ('PS_SECURITY_TOKEN', '1', NOW(), NOW())
;

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
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
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

/* Auto generated hooks added for version 8.0.0 */
INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionCreateProductFormBuilderModifier', 'Modify create product identifiable object form', 'This hook allows to modify create product identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionCombinationListFormBuilderModifier', 'Modify combination list identifiable object form', 'This hook allows to modify combination list identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionProductImageFormBuilderModifier', 'Modify product image identifiable object form', 'This hook allows to modify product image identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionSearchEngineFormBuilderModifier', 'Modify search engine identifiable object form', 'This hook allows to modify search engine identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionCategoryTreeSelectorFormBuilderModifier', 'Modify category tree selector identifiable object form', 'This hook allows to modify category tree selector identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionSqlRequestFormDataProviderData', 'Provide sql request identifiable object form data for update', 'This hook allows to provide sql request identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionCustomerFormDataProviderData', 'Provide customer identifiable object form data for update', 'This hook allows to provide customer identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionLanguageFormDataProviderData', 'Provide language identifiable object form data for update', 'This hook allows to provide language identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionCurrencyFormDataProviderData', 'Provide currency identifiable object form data for update', 'This hook allows to provide currency identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionWebserviceKeyFormDataProviderData', 'Provide webservice key identifiable object form data for update', 'This hook allows to provide webservice key identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionMetaFormDataProviderData', 'Provide meta identifiable object form data for update', 'This hook allows to provide meta identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionCategoryFormDataProviderData', 'Provide category identifiable object form data for update', 'This hook allows to provide category identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionRootCategoryFormDataProviderData', 'Provide root category identifiable object form data for update', 'This hook allows to provide root category identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionContactFormDataProviderData', 'Provide contact identifiable object form data for update', 'This hook allows to provide contact identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionCmsPageCategoryFormDataProviderData', 'Provide cms page category identifiable object form data for update', 'This hook allows to provide cms page category identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionTaxFormDataProviderData', 'Provide tax identifiable object form data for update', 'This hook allows to provide tax identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionManufacturerFormDataProviderData', 'Provide manufacturer identifiable object form data for update', 'This hook allows to provide manufacturer identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionEmployeeFormDataProviderData', 'Provide employee identifiable object form data for update', 'This hook allows to provide employee identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionProfileFormDataProviderData', 'Provide profile identifiable object form data for update', 'This hook allows to provide profile identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionCmsPageFormDataProviderData', 'Provide cms page identifiable object form data for update', 'This hook allows to provide cms page identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionFeatureFormDataProviderData', 'Provide feature identifiable object form data for update', 'This hook allows to provide feature identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionOrderMessageFormDataProviderData', 'Provide order message identifiable object form data for update', 'This hook allows to provide order message identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionCatalogPriceRuleFormDataProviderData', 'Provide catalog price rule identifiable object form data for update', 'This hook allows to provide catalog price rule identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionAttachmentFormDataProviderData', 'Provide attachment identifiable object form data for update', 'This hook allows to provide attachment identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionOrderStateFormDataProviderData', 'Provide order state identifiable object form data for update', 'This hook allows to provide order state identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionOrderReturnStateFormDataProviderData', 'Provide order return state identifiable object form data for update', 'This hook allows to provide order return state identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionCreateProductFormDataProviderData', 'Provide create product identifiable object form data for update', 'This hook allows to provide create product identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionCombinationListFormDataProviderData', 'Provide combination list identifiable object form data for update', 'This hook allows to provide combination list identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionProductImageFormDataProviderData', 'Provide product image identifiable object form data for update', 'This hook allows to provide product image identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionZoneFormDataProviderData', 'Provide zone identifiable object form data for update', 'This hook allows to provide zone identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionSearchEngineFormDataProviderData', 'Provide search engine identifiable object form data for update', 'This hook allows to provide search engine identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionCategoryTreeSelectorFormDataProviderData', 'Provide category tree selector identifiable object form data for update', 'This hook allows to provide category tree selector identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionSqlRequestFormDataProviderDefaultData', 'Provide sql request identifiable object default form data for creation', 'This hook allows to provide sql request identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionCustomerFormDataProviderDefaultData', 'Provide customer identifiable object default form data for creation', 'This hook allows to provide customer identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionLanguageFormDataProviderDefaultData', 'Provide language identifiable object default form data for creation', 'This hook allows to provide language identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionCurrencyFormDataProviderDefaultData', 'Provide currency identifiable object default form data for creation', 'This hook allows to provide currency identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionWebserviceKeyFormDataProviderDefaultData', 'Provide webservice key identifiable object default form data for creation', 'This hook allows to provide webservice key identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionMetaFormDataProviderDefaultData', 'Provide meta identifiable object default form data for creation', 'This hook allows to provide meta identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionCategoryFormDataProviderDefaultData', 'Provide category identifiable object default form data for creation', 'This hook allows to provide category identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionRootCategoryFormDataProviderDefaultData', 'Provide root category identifiable object default form data for creation', 'This hook allows to provide root category identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionContactFormDataProviderDefaultData', 'Provide contact identifiable object default form data for creation', 'This hook allows to provide contact identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionCmsPageCategoryFormDataProviderDefaultData', 'Provide cms page category identifiable object default form data for creation', 'This hook allows to provide cms page category identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionTaxFormDataProviderDefaultData', 'Provide tax identifiable object default form data for creation', 'This hook allows to provide tax identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionManufacturerFormDataProviderDefaultData', 'Provide manufacturer identifiable object default form data for creation', 'This hook allows to provide manufacturer identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionEmployeeFormDataProviderDefaultData', 'Provide employee identifiable object default form data for creation', 'This hook allows to provide employee identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionProfileFormDataProviderDefaultData', 'Provide profile identifiable object default form data for creation', 'This hook allows to provide profile identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionCmsPageFormDataProviderDefaultData', 'Provide cms page identifiable object default form data for creation', 'This hook allows to provide cms page identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionFeatureFormDataProviderDefaultData', 'Provide feature identifiable object default form data for creation', 'This hook allows to provide feature identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionOrderMessageFormDataProviderDefaultData', 'Provide order message identifiable object default form data for creation', 'This hook allows to provide order message identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionCatalogPriceRuleFormDataProviderDefaultData', 'Provide catalog price rule identifiable object default form data for creation', 'This hook allows to provide catalog price rule identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionAttachmentFormDataProviderDefaultData', 'Provide attachment identifiable object default form data for creation', 'This hook allows to provide attachment identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionOrderStateFormDataProviderDefaultData', 'Provide order state identifiable object default form data for creation', 'This hook allows to provide order state identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionOrderReturnStateFormDataProviderDefaultData', 'Provide order return state identifiable object default form data for creation', 'This hook allows to provide order return state identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionCreateProductFormDataProviderDefaultData', 'Provide create product identifiable object default form data for creation', 'This hook allows to provide create product identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionCombinationListFormDataProviderDefaultData', 'Provide combination list identifiable object default form data for creation', 'This hook allows to provide combination list identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionProductImageFormDataProviderDefaultData', 'Provide product image identifiable object default form data for creation', 'This hook allows to provide product image identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionZoneFormDataProviderDefaultData', 'Provide zone identifiable object default form data for creation', 'This hook allows to provide zone identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionSearchEngineFormDataProviderDefaultData', 'Provide search engine identifiable object default form data for creation', 'This hook allows to provide search engine identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionCategoryTreeSelectorFormDataProviderDefaultData', 'Provide category tree selector identifiable object default form data for creation', 'This hook allows to provide category tree selector identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionBeforeUpdateCreateProductFormHandler', 'Modify create product identifiable object data before updating it', 'This hook allows to modify create product identifiable object forms data before it was updated', '1'),
  (NULL, 'actionBeforeUpdateCombinationListFormHandler', 'Modify combination list identifiable object data before updating it', 'This hook allows to modify combination list identifiable object forms data before it was updated', '1'),
  (NULL, 'actionBeforeUpdateProductImageFormHandler', 'Modify product image identifiable object data before updating it', 'This hook allows to modify product image identifiable object forms data before it was updated', '1'),
  (NULL, 'actionBeforeUpdateSearchEngineFormHandler', 'Modify search engine identifiable object data before updating it', 'This hook allows to modify search engine identifiable object forms data before it was updated', '1'),
  (NULL, 'actionBeforeUpdateCategoryTreeSelectorFormHandler', 'Modify category tree selector identifiable object data before updating it', 'This hook allows to modify category tree selector identifiable object forms data before it was updated', '1'),
  (NULL, 'actionAfterUpdateCreateProductFormHandler', 'Modify create product identifiable object data after updating it', 'This hook allows to modify create product identifiable object forms data after it was updated', '1'),
  (NULL, 'actionAfterUpdateCombinationListFormHandler', 'Modify combination list identifiable object data after updating it', 'This hook allows to modify combination list identifiable object forms data after it was updated', '1'),
  (NULL, 'actionAfterUpdateSearchEngineFormHandler', 'Modify search engine identifiable object data after updating it', 'This hook allows to modify search engine identifiable object forms data after it was updated', '1'),
  (NULL, 'actionAfterUpdateCategoryTreeSelectorFormHandler', 'Modify category tree selector identifiable object data after updating it', 'This hook allows to modify category tree selector identifiable object forms data after it was updated', '1'),
  (NULL, 'actionBeforeCreateCreateProductFormHandler', 'Modify create product identifiable object data before creating it', 'This hook allows to modify create product identifiable object forms data before it was created', '1'),
  (NULL, 'actionBeforeCreateCombinationListFormHandler', 'Modify combination list identifiable object data before creating it', 'This hook allows to modify combination list identifiable object forms data before it was created', '1'),
  (NULL, 'actionBeforeCreateProductImageFormHandler', 'Modify product image identifiable object data before creating it', 'This hook allows to modify product image identifiable object forms data before it was created', '1'),
  (NULL, 'actionBeforeCreateSearchEngineFormHandler', 'Modify search engine identifiable object data before creating it', 'This hook allows to modify search engine identifiable object forms data before it was created', '1'),
  (NULL, 'actionBeforeCreateCategoryTreeSelectorFormHandler', 'Modify category tree selector identifiable object data before creating it', 'This hook allows to modify category tree selector identifiable object forms data before it was created', '1'),
  (NULL, 'actionAfterCreateCreateProductFormHandler', 'Modify create product identifiable object data after creating it', 'This hook allows to modify create product identifiable object forms data after it was created', '1'),
  (NULL, 'actionAfterCreateCombinationListFormHandler', 'Modify combination list identifiable object data after creating it', 'This hook allows to modify combination list identifiable object forms data after it was created', '1'),
  (NULL, 'actionAfterCreateProductImageFormHandler', 'Modify product image identifiable object data after creating it', 'This hook allows to modify product image identifiable object forms data after it was created', '1'),
  (NULL, 'actionAfterCreateSearchEngineFormHandler', 'Modify search engine identifiable object data after creating it', 'This hook allows to modify search engine identifiable object forms data after it was created', '1'),
  (NULL, 'actionAfterCreateCategoryTreeSelectorFormHandler', 'Modify category tree selector identifiable object data after creating it', 'This hook allows to modify category tree selector identifiable object forms data after it was created', '1'),
  (NULL, 'actionFeatureFlagStableForm', 'Modify feature flag stable options form content', 'This hook allows to modify feature flag stable options form FormBuilder', '1'),
  (NULL, 'actionFeatureFlagBetaForm', 'Modify feature flag beta options form content', 'This hook allows to modify feature flag beta options form FormBuilder', '1'),
  (NULL, 'actionSecurityPageGeneralForm', 'Modify security page general options form content', 'This hook allows to modify security page general options form FormBuilder', '1'),
  (NULL, 'actionSecurityPagePasswordPolicyForm', 'Modify security page password policy options form content', 'This hook allows to modify security page password policy options form FormBuilder', '1'),
  (NULL, 'actionFeatureFlagStableSave', 'Modify feature flag stable options form saved data', 'This hook allows to modify data of feature flag stable options form after it was saved', '1'),
  (NULL, 'actionFeatureFlagBetaSave', 'Modify feature flag beta options form saved data', 'This hook allows to modify data of feature flag beta options form after it was saved', '1'),
  (NULL, 'actionSecurityPageGeneralSave', 'Modify security page general options form saved data', 'This hook allows to modify data of security page general options form after it was saved', '1'),
  (NULL, 'actionSecurityPagePasswordPolicySave', 'Modify security page password policy options form saved data', 'This hook allows to modify data of security page password policy options form after it was saved', '1'),
  (NULL, 'actionCountryGridDefinitionModifier', 'Modify country grid definition', 'This hook allows to alter country grid columns, actions and filters', '1'),
  (NULL, 'actionSearchEngineGridDefinitionModifier', 'Modify search engine grid definition', 'This hook allows to alter search engine grid columns, actions and filters', '1'),
  (NULL, 'actionProductGridDefinitionModifier', 'Modify product grid definition', 'This hook allows to alter product grid columns, actions and filters', '1'),
  (NULL, 'actionProductGridDefinitionModifier', 'Modify product grid definition', 'This hook allows to alter product grid columns, actions and filters', '1'),
  (NULL, 'actionSecuritySessionEmployeeGridDefinitionModifier', 'Modify security session employee grid definition', 'This hook allows to alter security session employee grid columns, actions and filters', '1'),
  (NULL, 'actionSecuritySessionCustomerGridDefinitionModifier', 'Modify security session customer grid definition', 'This hook allows to alter security session customer grid columns, actions and filters', '1'),
  (NULL, 'actionStateGridDefinitionModifier', 'Modify state grid definition', 'This hook allows to alter state grid columns, actions and filters', '1'),
  (NULL, 'actionTitleGridDefinitionModifier', 'Modify title grid definition', 'This hook allows to alter title grid columns, actions and filters', '1'),
  (NULL, 'actionCountryGridQueryBuilderModifier', 'Modify country grid query builder', 'This hook allows to alter Doctrine query builder for country grid', '1'),
  (NULL, 'actionSearchEngineGridQueryBuilderModifier', 'Modify search engine grid query builder', 'This hook allows to alter Doctrine query builder for search engine grid', '1'),
  (NULL, 'actionProductGridQueryBuilderModifier', 'Modify product grid query builder', 'This hook allows to alter Doctrine query builder for product grid', '1'),
  (NULL, 'actionProductGridQueryBuilderModifier', 'Modify product grid query builder', 'This hook allows to alter Doctrine query builder for product grid', '1'),
  (NULL, 'actionSecuritySessionEmployeeGridQueryBuilderModifier', 'Modify security session employee grid query builder', 'This hook allows to alter Doctrine query builder for security session employee grid', '1'),
  (NULL, 'actionSecuritySessionCustomerGridQueryBuilderModifier', 'Modify security session customer grid query builder', 'This hook allows to alter Doctrine query builder for security session customer grid', '1'),
  (NULL, 'actionStateGridQueryBuilderModifier', 'Modify state grid query builder', 'This hook allows to alter Doctrine query builder for state grid', '1'),
  (NULL, 'actionTitleGridQueryBuilderModifier', 'Modify title grid query builder', 'This hook allows to alter Doctrine query builder for title grid', '1'),
  (NULL, 'actionCountryGridDataModifier', 'Modify country grid data', 'This hook allows to modify country grid data', '1'),
  (NULL, 'actionSearchEngineGridDataModifier', 'Modify search engine grid data', 'This hook allows to modify search engine grid data', '1'),
  (NULL, 'actionProductGridDataModifier', 'Modify product grid data', 'This hook allows to modify product grid data', '1'),
  (NULL, 'actionProductGridDataModifier', 'Modify product grid data', 'This hook allows to modify product grid data', '1'),
  (NULL, 'actionSecuritySessionEmployeeGridDataModifier', 'Modify security session employee grid data', 'This hook allows to modify security session employee grid data', '1'),
  (NULL, 'actionSecuritySessionCustomerGridDataModifier', 'Modify security session customer grid data', 'This hook allows to modify security session customer grid data', '1'),
  (NULL, 'actionStateGridDataModifier', 'Modify state grid data', 'This hook allows to modify state grid data', '1'),
  (NULL, 'actionTitleGridDataModifier', 'Modify title grid data', 'This hook allows to modify title grid data', '1'),
  (NULL, 'actionCountryGridFilterFormModifier', 'Modify country grid filters', 'This hook allows to modify filters for country grid', '1'),
  (NULL, 'actionSearchEngineGridFilterFormModifier', 'Modify search engine grid filters', 'This hook allows to modify filters for search engine grid', '1'),
  (NULL, 'actionProductGridFilterFormModifier', 'Modify product grid filters', 'This hook allows to modify filters for product grid', '1'),
  (NULL, 'actionProductGridFilterFormModifier', 'Modify product grid filters', 'This hook allows to modify filters for product grid', '1'),
  (NULL, 'actionSecuritySessionEmployeeGridFilterFormModifier', 'Modify security session employee grid filters', 'This hook allows to modify filters for security session employee grid', '1'),
  (NULL, 'actionSecuritySessionCustomerGridFilterFormModifier', 'Modify security session customer grid filters', 'This hook allows to modify filters for security session customer grid', '1'),
  (NULL, 'actionStateGridFilterFormModifier', 'Modify state grid filters', 'This hook allows to modify filters for state grid', '1'),
  (NULL, 'actionTitleGridFilterFormModifier', 'Modify title grid filters', 'This hook allows to modify filters for title grid', '1'),
  (NULL, 'actionCountryGridPresenterModifier', 'Modify country grid template data', 'This hook allows to modify data which is about to be used in template for country grid', '1'),
  (NULL, 'actionSearchEngineGridPresenterModifier', 'Modify search engine grid template data', 'This hook allows to modify data which is about to be used in template for search engine grid', '1'),
  (NULL, 'actionProductGridPresenterModifier', 'Modify product grid template data', 'This hook allows to modify data which is about to be used in template for product grid', '1'),
  (NULL, 'actionProductGridPresenterModifier', 'Modify product grid template data', 'This hook allows to modify data which is about to be used in template for product grid', '1'),
  (NULL, 'actionSecuritySessionEmployeeGridPresenterModifier', 'Modify security session employee grid template data', 'This hook allows to modify data which is about to be used in template for security session employee grid', '1'),
  (NULL, 'actionSecuritySessionCustomerGridPresenterModifier', 'Modify security session customer grid template data', 'This hook allows to modify data which is about to be used in template for security session customer grid', '1'),
  (NULL, 'actionStateGridPresenterModifier', 'Modify state grid template data', 'This hook allows to modify data which is about to be used in template for state grid', '1'),
  (NULL, 'actionTitleGridPresenterModifier', 'Modify title grid template data', 'This hook allows to modify data which is about to be used in template for title grid', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

ALTER TABLE `PREFIX_employee_session` ADD `date_upd` DATETIME NOT NULL AFTER `token`;
ALTER TABLE `PREFIX_employee_session` ADD `date_add` DATETIME NOT NULL AFTER `date_upd`;
UPDATE `PREFIX_employee_session` SET `date_add` = NOW(), `date_upd` = NOW();
ALTER TABLE `PREFIX_customer_session` ADD `date_upd` DATETIME NOT NULL AFTER `token`;
ALTER TABLE `PREFIX_customer_session` ADD `date_add` DATETIME NOT NULL AFTER `date_upd`;
UPDATE `PREFIX_customer_session` SET `date_add` = NOW(), `date_upd` = NOW();

ALTER TABLE `PREFIX_carrier` DROP COLUMN `id_tax_rules_group`;

ALTER TABLE `PREFIX_category_lang` ADD `additional_description` text AFTER `description`;

ALTER TABLE `PREFIX_product` MODIFY COLUMN `redirect_type` ENUM(
    '404', '410', '301-product', '302-product', '301-category', '302-category'
) NOT NULL DEFAULT '404';
ALTER TABLE `PREFIX_product_shop` MODIFY COLUMN `redirect_type` ENUM(
    '', '404', '410', '301-product', '302-product', '301-category', '302-category'
) NOT NULL DEFAULT '';

/* PHP:ps_800_add_security_tab(); */;

ALTER TABLE `PREFIX_order_detail` MODIFY COLUMN `product_name` TEXT NOT NULL;

ALTER TABLE `PREFIX_webservice_permission` MODIFY COLUMN `method` ENUM(
    'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'
) NOT NULL;

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

/* Insert new password policy configuration values */
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
  ('PS_SECURITY_PASSWORD_POLICY_MAXIMUM_LENGTH', '72', NOW(), NOW()),
  ('PS_SECURITY_PASSWORD_POLICY_MINIMUM_LENGTH', '8', NOW(), NOW()),
  ('PS_SECURITY_PASSWORD_POLICY_MINIMUM_SCORE', '3', NOW(), NOW())
;

UPDATE `PREFIX_carrier` SET `name` = 'Click and collect' WHERE `name` = '0';

/* PHP:install_ps_distributionapiclient(); */;

/* Remove deprecated columns */
/* PHP:drop_column_if_exists('product_attribute', 'location'); */;
/* PHP:drop_column_if_exists('product_attribute', 'quantity'); */;
/* PHP:drop_column_if_exists('orders', 'shipping_number'); */;

ALTER TABLE `PREFIX_tab` DROP hide_host_mode;
ALTER TABLE `PREFIX_feature_flag` CHANGE label_wording label_wording VARCHAR(512) DEFAULT '' NOT NULL;
ALTER TABLE `PREFIX_feature_flag` CHANGE description_wording description_wording VARCHAR(512) DEFAULT '' NOT NULL;
