SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/* 
  Insert new feature flags introduced for the newly improved shipment system
  https://github.com/PrestaShop/PrestaShop/pull/38040
  Insert new feature flags introduced for the migration of tag page
  https://github.com/PrestaShop/PrestaShop/pull/39516
*/
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('improved_shipment', 'env,dotenv,db', 'Improved shipment', 'Admin.Advparameters.Feature', 'Enable / Disable the newly improved shipment system', 'Admin.Advparameters.Help', 0, 'beta'),
  ('discount', 'env,dotenv,db', 'Discount', 'Admin.Advparameters.Feature', 'Enable / Disable the new discount system.', 'Admin.Advparameters.Help', 0, 'beta'),
  ('tag', 'env,dotenv,db', 'Tag', 'Admin.Advparameters.Feature', 'Enable / Disable the tag page.', 'Admin.Advparameters.Help', 0, 'beta');

/* Add a new field to cart_rule */
/* https://github.com/PrestaShop/PrestaShop/pull/37911/ */
/* PHP:add_column('cart_rule', 'type', 'VARCHAR(128) DEFAULT NULL'); */;
CREATE INDEX `type` ON `PREFIX_cart_rule` (`type`);

/* Insert new shipment table */
/* https://github.com/PrestaShop/PrestaShop/pull/38046 */
CREATE TABLE IF NOT EXISTS `PREFIX_shipment` (
  `id_shipment` int(10) AUTO_INCREMENT NOT NULL,
  `id_order` int(10) NOT NULL,
  `id_carrier` int(10) NOT NULL,
  `id_delivery_address` int(10) DEFAULT NULL,
  `shipping_cost_tax_excl` NUMERIC(20, 6) DEFAULT '0.000000',
  `shipping_cost_tax_incl` NUMERIC(20, 6) DEFAULT '0.000000',
  `packed_at` datetime DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `cancelled_at` DATETIME DEFAULT NULL,
  `tracking_number` varchar(255) DEFAULT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_shipment`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_shipment_product` (
  `id_shipment_product` INT AUTO_INCREMENT NOT NULL,
  `id_shipment` int(10) NOT NULL,
  `id_order_detail` int(10) NOT NULL,
  `quantity` int(10) DEFAULT NULL,
  PRIMARY KEY (id_shipment_product)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `PREFIX_cart_rule_product_rule` MODIFY COLUMN `type` ENUM(
    'products', 'categories', 'attributes',
    'manufacturers', 'suppliers', 'combinations', 'features'
) NOT NULL;

/* PHP:add_column('cart_rule_product_rule_group', 'type', 'ENUM("at_least_one_product_rule", "all_product_rules") NOT NULL DEFAULT "at_least_one_product_rule"'); */;

/* Auto generated hooks added for version 9.1.0 */
INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionModuleUpgradeAfter', '', '', '1'),
  (NULL, 'actionModuleEnable', '', '', '1'),
  (NULL, 'actionModuleDisable', '', '', '1'),
  (NULL, 'actionConfigurationUpdateValueBefore', '', '', '1'),
  (NULL, 'actionAdminDuplicateDiscountBefore', '', '', '1'),
  (NULL, 'actionAdminDuplicateDiscountAfter', '', '', '1'),
  (NULL, 'actionFacetedSearchSetSupportedControllers', '', '', '1'),
  (NULL, 'actionFacetedSearchFilters', '', '', '1'),
  (NULL, 'actionMainMenuModifier', '', '', '1'),
  (NULL, 'actionTagFormBuilderModifier', 'Modify tag identifiable object form', 'This hook allows to modify tag identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionTagFormDataProviderData', 'Provide tag identifiable object form data for update', 'This hook allows to provide tag identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionTagFormDataProviderDefaultData', 'Provide tag identifiable object default form data for creation', 'This hook allows to provide tag identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionBeforeUpdateTagFormHandler', 'Modify tag identifiable object data before updating it', 'This hook allows to modify tag identifiable object forms data before it was updated', '1'),
  (NULL, 'actionAfterUpdateTagFormHandler', 'Modify tag identifiable object data after updating it', 'This hook allows to modify tag identifiable object forms data after it was updated', '1'),
  (NULL, 'actionBeforeCreateTagFormHandler', 'Modify tag identifiable object data before creating it', 'This hook allows to modify tag identifiable object forms data before it was created', '1'),
  (NULL, 'actionAfterCreateTagFormHandler', 'Modify tag identifiable object data after creating it', 'This hook allows to modify tag identifiable object forms data after it was created', '1'),
  (NULL, 'actionDiscountGridDefinitionModifier', 'Modify discount grid definition', 'This hook allows to alter discount grid columns, actions and filters', '1'),
  (NULL, 'actionDiscountGridQueryBuilderModifier', 'Modify discount grid query builder', 'This hook allows to alter Doctrine query builder for discount grid', '1'),
  (NULL, 'actionDiscountGridDataModifier', 'Modify discount grid data', 'This hook allows to modify discount grid data', '1'),
  (NULL, 'actionDiscountGridFilterFormModifier', 'Modify discount grid filters', 'This hook allows to modify filters for discount grid', '1'),
  (NULL, 'actionDiscountGridPresenterModifier', 'Modify discount grid template data', 'This hook allows to modify data which is about to be used in template for discount grid', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);
