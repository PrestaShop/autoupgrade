SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/40867
-- Change date_to field to make it nullable in cart_rule
ALTER TABLE `PREFIX_cart_rule` CHANGE `date_to` `date_to` datetime DEFAULT NULL;

-- https://github.com/PrestaShop/PrestaShop/pull/40830
/* PHP:add_column('cart_rule', 'total_quantity', 'int(10) UNSIGNED DEFAULT NULL AFTER `minimum_product_quantity`'); */;

-- Populate the new total_quantity column for existing cart rules.
-- Previously, the `quantity` field represented the number of uses LEFT (decremented on each use).
-- The new `total_quantity` field represents the ORIGINAL total number of allowed uses.
-- Formula: total_quantity = quantity (remaining) + quantityUsed (consumed in non-error orders)
-- Cart rules with quantity IS NULL are unlimited and keep total_quantity as NULL.
-- The subquery mirrors the logic from DiscountRepository::getQuantityUsedInOrders,
-- counting non-deleted order_cart_rule entries on orders not in error state.
UPDATE `PREFIX_cart_rule` cr
LEFT JOIN (
    SELECT ocr.`id_cart_rule`, COUNT(*) as quantity_used
    FROM `PREFIX_order_cart_rule` ocr
    INNER JOIN `PREFIX_orders` o ON ocr.`id_order` = o.`id_order`
    WHERE ocr.`deleted` = 0
    AND o.`current_state` != (
        SELECT CAST(`value` AS UNSIGNED)
        FROM `PREFIX_configuration`
        WHERE `name` = 'PS_OS_ERROR'
    )
    GROUP BY ocr.`id_cart_rule`
) used ON used.`id_cart_rule` = cr.`id_cart_rule`
SET cr.`total_quantity` = cr.`quantity` + COALESCE(used.`quantity_used`, 0)
WHERE cr.`quantity` IS NOT NULL;

-- https://github.com/PrestaShop/PrestaShop/pull/41407
-- Insert new feature flag introduced for the migration of the Hook a module page
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('hook_module_v2', 'env,dotenv,db', 'Hook a module', 'Admin.Design.Feature', 'Enable / Disable the migrated Hook a module form page.', 'Admin.Design.Help', 0, 'stable');


-- New B2B feature

-- https://github.com/PrestaShop/PrestaShop/pull/40224
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('improved_b2b', 'env,dotenv,db', 'Improved B2B', 'Admin.Advparameters.Feature', 'Enable / Disable the improved B2B mode. To use the feature activate the B2B mode in General Settings', 'Admin.Advparameters.Help', 0, 'beta');

-- https://github.com/PrestaShop/PrestaShop/pull/40632
-- Insert B2B foundation
CREATE TABLE IF NOT EXISTS `PREFIX_business_entity` (
  `id_business_entity` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_shop` INT UNSIGNED NOT NULL,
  `id_customer_group` INT UNSIGNED NOT NULL,
  `external_ref` VARCHAR(255) DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `legal_name` VARCHAR(255) DEFAULT NULL,
  `delivery_authorized` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM ('pending', 'active', 'inactive', 'rejected') NOT NULL DEFAULT 'pending',
  `deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id_business_entity`),
  KEY `business_entity_shop_idx` (`id_shop`),
  KEY `business_entity_customer_group_idx` (`id_customer_group`),
  KEY `business_entity_external_ref_idx` (`external_ref`),
  KEY `business_entity_deleted_idx` (`deleted`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_customer_b2b` (
  `id_customer_b2b` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_customer` INT UNSIGNED NOT NULL,
  `status` ENUM ('pending', 'active', 'rejected') NOT NULL DEFAULT 'pending',
  `external_ref` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_customer_b2b_customer` (`id_customer`),
  PRIMARY KEY (`id_customer_b2b`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_business_entity_customer_b2b` (
  `id_business_entity_customer_b2b` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_business_entity` INT UNSIGNED NOT NULL,
  `id_customer_b2b` INT UNSIGNED NOT NULL,
  `id_role` INT UNSIGNED NOT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id_business_entity_customer_b2b`),
  UNIQUE KEY `uniq_be_customer` (`id_business_entity`, `id_customer_b2b`),
  KEY `business_entity_customer_b2b_be_idx` (`id_business_entity`),
  KEY `business_entity_customer_b2b_customer_idx` (`id_customer_b2b`),
  KEY `business_entity_customer_b2b_role_idx` (`id_role`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_business_entity_identifier` (
  `id_identifier` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_business_entity` INT UNSIGNED NOT NULL,
  `id_business_identifier` INT UNSIGNED NOT NULL,
  `value` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id_identifier`),
  UNIQUE KEY `uniq_business_entity_identifier` (`id_business_entity`, `id_business_identifier`),
  KEY `business_entity_identifier_id_business_entity_idx` (`id_business_entity`),
  KEY `business_entity_identifier_id_business_identifier_idx` (`id_business_identifier`),
  KEY `business_entity_identifier_value_idx` (`value`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_business_identifier` (
  `id_business_identifier` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `label` VARCHAR(255) NOT NULL,
  `unremovable` TINYINT(1) NOT NULL DEFAULT 0,
  `id_zone` INT UNSIGNED DEFAULT NULL,
  `deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id_business_identifier`),
  KEY `business_identifier_zone_idx` (`id_zone`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_business_entity_address` (
  `id_business_entity_address` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_business_entity` INT UNSIGNED NOT NULL,
  `id_address` INT UNSIGNED NOT NULL,
  `address_type` ENUM ('both', 'invoice', 'delivery') NOT NULL DEFAULT 'both',
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id_business_entity_address`),
  UNIQUE KEY `uniq_be_address` (`id_business_entity`, `id_address`, `address_type`),
  KEY `business_entity_address_be_idx` (`id_business_entity`),
  KEY `business_entity_address_address_idx` (`id_address`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_b2b_role` (
  `id_role` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `role` VARCHAR(64) NOT NULL,
  UNIQUE KEY `uniq_b2b_role` (`role`),
  PRIMARY KEY (`id_role`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_b2b_role_authorization_role` (
  `id_role` INT UNSIGNED NOT NULL,
  `id_authorization_role` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id_role`, `id_authorization_role`),
  KEY `b2b_role_authorization_role_role_idx` (`id_role`),
  KEY `b2b_role_authorization_role_auth_role_idx` (`id_authorization_role`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- https://github.com/PrestaShop/PrestaShop/pull/40685
/* PHP:add_configuration_if_not_exists('PS_SHOP_MODE', 'b2c_only'); */;

-- https://github.com/PrestaShop/PrestaShop/pull/41028
/* PHP:ps_920_business_entities_tabs(); */;

-- End of B2B feature

-- https://github.com/PrestaShop/PrestaShop/pull/41092
-- Create the extra property definition registry table.
CREATE TABLE IF NOT EXISTS `PREFIX_extra_property_definition` (
  `id_extra_property_definition` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entity_name` varchar(64) NOT NULL,
  `module_name` varchar(64) DEFAULT NULL,
  `property_name` varchar(64) NOT NULL,
  `type` ENUM ('int','bool','string','float','date','html','json','choice') NOT NULL DEFAULT 'string',
  `scope` ENUM ('common','lang','shop') NOT NULL DEFAULT 'common',
  `sql_index` ENUM ('none','key','unique') NOT NULL DEFAULT 'none',
  `size` smallint(5) unsigned DEFAULT NULL,
  `default_value` varchar(255) DEFAULT NULL,
  `required` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `constraints` longtext DEFAULT NULL,
  `display_front` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `associated_apis` text DEFAULT NULL,
  `associated_grids` text DEFAULT NULL,
  `associated_forms` text DEFAULT NULL,
  `form_type` varchar(255) DEFAULT NULL,
  `form_options` text DEFAULT NULL,
  `label_wording` varchar(191) DEFAULT NULL,
  `label_domain` varchar(255) DEFAULT NULL,
  `description_wording` varchar(191) DEFAULT NULL,
  `description_domain` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_extra_property_definition`),
  UNIQUE KEY `extra_property_definition_unique` (`entity_name`, `module_name`, `property_name`),
  KEY `entity_name` (`entity_name`, `scope`),
  KEY `module_name` (`module_name`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- https://github.com/PrestaShop/PrestaShop/pull/41775
/* PHP:ps_920_extra_property_definitions_tab(); */;

-- https://github.com/PrestaShop/PrestaShop/pull/41546
-- Add column cancelled field to define an order return detail as cancelled
/* PHP:add_column('order_return_detail', 'cancelled', 'tinyint(1) unsigned NOT NULL DEFAULT \'0\''); */;
-- Add column is_cancelling_return field to define if it cancels returns
/* PHP:add_column('order_return_state', 'is_cancelling_return', 'tinyint(1) unsigned NOT NULL DEFAULT \'0\''); */;

-- https://github.com/PrestaShop/PrestaShop/pull/40031
/* Extend the product condition enum with new values */
ALTER TABLE `PREFIX_product` MODIFY COLUMN `condition`
  ENUM('new', 'used', 'refurbished', 'open_box', 'damaged', 'new_with_defects') NOT NULL DEFAULT 'new';
ALTER TABLE `PREFIX_product_shop` MODIFY COLUMN `condition`
  ENUM('new', 'used', 'refurbished', 'open_box', 'damaged', 'new_with_defects') NOT NULL DEFAULT 'new';

-- https://github.com/PrestaShop/PrestaShop/pull/40238
/* Mark the "tag" feature flag as stable */
UPDATE `PREFIX_feature_flag` SET `stability` = 'stable' WHERE `name` = 'tag';

-- https://github.com/PrestaShop/PrestaShop/pull/41032
/* New pricing feature flag (beta) */
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('new_pricing', 'env,query,dotenv,db', 'New pricing', 'Admin.Advparameters.Feature', 'Enable / Disable the new pricing system. This feature introduces an improved pricing engine.', 'Admin.Advparameters.Help', 0, 'beta');

-- https://github.com/PrestaShop/PrestaShop/pull/41508 + https://github.com/PrestaShop/PrestaShop/pull/41630
/* Quick Access migrated to Symfony: add the (already stable) feature flag, then move the existing AdminQuickAccesses tab under Advanced Parameters and create its missing permissions */
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('quick_access', 'env,dotenv,db', 'Quick access', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated quick access page.', 'Admin.Advparameters.Help', 0, 'stable');
/* PHP:ps_920_quick_access_tab(); */;

-- https://github.com/PrestaShop/PrestaShop/pull/41433
-- https://github.com/PrestaShop/PrestaShop/pull/41627
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('email_body_translation', 'env,dotenv,db', 'Email body translations', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated email body translations page.', 'Admin.Advparameters.Help', 0, 'stable');

-- https://github.com/PrestaShop/PrestaShop/pull/41662
UPDATE `PREFIX_feature_flag` SET `stability` = 'stable' WHERE `name` = 'merchandise_return';

-- https://github.com/PrestaShop/PrestaShop/pull/41594
UPDATE `PREFIX_feature_flag` SET `stability` = 'stable' WHERE `name` = 'country';

-- https://github.com/PrestaShop/PrestaShop/pull/41777
UPDATE `PREFIX_feature_flag` SET `stability` = 'stable' WHERE `name` = 'tax_rules_group';

-- https://github.com/PrestaShop/PrestaShop/pull/40852
/* PHP:add_column('shipment', 'deleted', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `tracking_number`'); */;

-- https://github.com/PrestaShop/PrestaShop/pull/42024
DELETE FROM `PREFIX_feature_flag`  WHERE `name` = 'state';

-- https://github.com/PrestaShop/PrestaShop/pull/41977
/* PHP:add_column('image_type', 'image_fitment', 'ENUM(\'fit\', \'crop\', \'bound\') NOT NULL DEFAULT \'fit\' AFTER height'); */;

/* Auto generated hooks added for version 9.2.0 */
INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionCheckoutBuildProcess', 'Build checkout process', 'This hook is triggered before the checkout is rendered. Modules may return a checkout process provider. The provider is used only when exactly one enabled and valid provider is available.', '1'),
  (NULL, 'displaySubcategories', 'Display content in subcategory list', 'This hooks allows you to display additional items in a list of subcategories in front office. Related blog posts, links to other categories, landing pages etc.', '1'),
  (NULL, 'displayCartBelowSummary', 'Display content below summary on cart page', 'Allows you to display content on front office cart page, below the totals summary.', '1'),
  (NULL, 'actionExtraPropertyDefinitionFormBuilderModifier', 'Modify extra property definition identifiable object form', 'This hook allows to modify extra property definition identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionTaxRuleFormBuilderModifier', 'Modify tax rule identifiable object form', 'This hook allows to modify tax rule identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionFulfillShipmentFormBuilderModifier', 'Modify fulfill shipment identifiable object form', 'This hook allows to modify fulfill shipment identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionQuickAccessFormBuilderModifier', 'Modify quick access identifiable object form', 'This hook allows to modify quick access identifiable object forms content by modifying form builder data or FormBuilder itself', '1'),
  (NULL, 'actionExtraPropertyDefinitionFormDataProviderData', 'Provide extra property definition identifiable object form data for update', 'This hook allows to provide extra property definition identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionTaxRuleFormDataProviderData', 'Provide tax rule identifiable object form data for update', 'This hook allows to provide tax rule identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionFulfillShipmentFormDataProviderData', 'Provide fulfill shipment identifiable object form data for update', 'This hook allows to provide fulfill shipment identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionQuickAccessFormDataProviderData', 'Provide quick access identifiable object form data for update', 'This hook allows to provide quick access identifiable object form data which will prefill the form in update/edition page', '1'),
  (NULL, 'actionExtraPropertyDefinitionFormDataProviderDefaultData', 'Provide extra property definition identifiable object default form data for creation', 'This hook allows to provide extra property definition identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionTaxRuleFormDataProviderDefaultData', 'Provide tax rule identifiable object default form data for creation', 'This hook allows to provide tax rule identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionFulfillShipmentFormDataProviderDefaultData', 'Provide fulfill shipment identifiable object default form data for creation', 'This hook allows to provide fulfill shipment identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionQuickAccessFormDataProviderDefaultData', 'Provide quick access identifiable object default form data for creation', 'This hook allows to provide quick access identifiable object form data which will prefill the form in creation page', '1'),
  (NULL, 'actionBeforeUpdateExtraPropertyDefinitionFormHandler', 'Modify extra property definition identifiable object data before updating it', 'This hook allows to modify extra property definition identifiable object forms data before it was updated', '1'),
  (NULL, 'actionBeforeUpdateTaxRuleFormHandler', 'Modify tax rule identifiable object data before updating it', 'This hook allows to modify tax rule identifiable object forms data before it was updated', '1'),
  (NULL, 'actionBeforeUpdateFulfillShipmentFormHandler', 'Modify fulfill shipment identifiable object data before updating it', 'This hook allows to modify fulfill shipment identifiable object forms data before it was updated', '1'),
  (NULL, 'actionBeforeUpdateQuickAccessFormHandler', 'Modify quick access identifiable object data before updating it', 'This hook allows to modify quick access identifiable object forms data before it was updated', '1'),
  (NULL, 'actionAfterUpdateExtraPropertyDefinitionFormHandler', 'Modify extra property definition identifiable object data after updating it', 'This hook allows to modify extra property definition identifiable object forms data after it was updated', '1'),
  (NULL, 'actionAfterUpdateTaxRuleFormHandler', 'Modify tax rule identifiable object data after updating it', 'This hook allows to modify tax rule identifiable object forms data after it was updated', '1'),
  (NULL, 'actionAfterUpdateFulfillShipmentFormHandler', 'Modify fulfill shipment identifiable object data after updating it', 'This hook allows to modify fulfill shipment identifiable object forms data after it was updated', '1'),
  (NULL, 'actionAfterUpdateQuickAccessFormHandler', 'Modify quick access identifiable object data after updating it', 'This hook allows to modify quick access identifiable object forms data after it was updated', '1'),
  (NULL, 'actionBeforeCreateExtraPropertyDefinitionFormHandler', 'Modify extra property definition identifiable object data before creating it', 'This hook allows to modify extra property definition identifiable object forms data before it was created', '1'),
  (NULL, 'actionBeforeCreateTaxRuleFormHandler', 'Modify tax rule identifiable object data before creating it', 'This hook allows to modify tax rule identifiable object forms data before it was created', '1'),
  (NULL, 'actionBeforeCreateFulfillShipmentFormHandler', 'Modify fulfill shipment identifiable object data before creating it', 'This hook allows to modify fulfill shipment identifiable object forms data before it was created', '1'),
  (NULL, 'actionBeforeCreateQuickAccessFormHandler', 'Modify quick access identifiable object data before creating it', 'This hook allows to modify quick access identifiable object forms data before it was created', '1'),
  (NULL, 'actionAfterCreateExtraPropertyDefinitionFormHandler', 'Modify extra property definition identifiable object data after creating it', 'This hook allows to modify extra property definition identifiable object forms data after it was created', '1'),
  (NULL, 'actionAfterCreateTaxRuleFormHandler', 'Modify tax rule identifiable object data after creating it', 'This hook allows to modify tax rule identifiable object forms data after it was created', '1'),
  (NULL, 'actionAfterCreateFulfillShipmentFormHandler', 'Modify fulfill shipment identifiable object data after creating it', 'This hook allows to modify fulfill shipment identifiable object forms data after it was created', '1'),
  (NULL, 'actionAfterCreateQuickAccessFormHandler', 'Modify quick access identifiable object data after creating it', 'This hook allows to modify quick access identifiable object forms data after it was created', '1'),
  (NULL, 'actionCountriesPageOptionsForm', 'Modify countries page options options form content', 'This hook allows to modify countries page options options form FormBuilder', '1'),
  (NULL, 'actionOrderReturnForm', 'Modify order return options form content', 'This hook allows to modify order return options form FormBuilder', '1'),
  (NULL, 'actionCountriesPageOptionsSave', 'Modify countries page options options form saved data', 'This hook allows to modify data of countries page options options form after it was saved', '1'),
  (NULL, 'actionOrderReturnSave', 'Modify order return options form saved data', 'This hook allows to modify data of order return options form after it was saved', '1'),
  (NULL, 'actionEmailBodyTemplateGridDefinitionModifier', 'Modify email body template grid definition', 'This hook allows to alter email body template grid columns, actions and filters', '1'),
  (NULL, 'actionExtraPropertyDefinitionGridDefinitionModifier', 'Modify extra property definition grid definition', 'This hook allows to alter extra property definition grid columns, actions and filters', '1'),
  (NULL, 'actionEmailBodyTemplateGridQueryBuilderModifier', 'Modify email body template grid query builder', 'This hook allows to alter Doctrine query builder for email body template grid', '1'),
  (NULL, 'actionExtraPropertyDefinitionGridQueryBuilderModifier', 'Modify extra property definition grid query builder', 'This hook allows to alter Doctrine query builder for extra property definition grid', '1'),
  (NULL, 'actionEmailBodyTemplateGridDataModifier', 'Modify email body template grid data', 'This hook allows to modify email body template grid data', '1'),
  (NULL, 'actionExtraPropertyDefinitionGridDataModifier', 'Modify extra property definition grid data', 'This hook allows to modify extra property definition grid data', '1'),
  (NULL, 'actionEmailBodyTemplateGridFilterFormModifier', 'Modify email body template grid filters', 'This hook allows to modify filters for email body template grid', '1'),
  (NULL, 'actionExtraPropertyDefinitionGridFilterFormModifier', 'Modify extra property definition grid filters', 'This hook allows to modify filters for extra property definition grid', '1'),
  (NULL, 'actionEmailBodyTemplateGridPresenterModifier', 'Modify email body template grid template data', 'This hook allows to modify data which is about to be used in template for email body template grid', '1'),
  (NULL, 'actionExtraPropertyDefinitionGridPresenterModifier', 'Modify extra property definition grid template data', 'This hook allows to modify data which is about to be used in template for extra property definition grid', '1'),
  -- also declared in 9.1.5.sql, where it belonged: repeated here for shops already
  -- upgraded to 9.1.5, which no longer run that script
  -- https://github.com/PrestaShop/PrestaShop/pull/41824
  (NULL, 'actionNotFound', 'Action when a page is not found', 'Allows modules to react when a page is not found - log it, redirect or perform other actions.', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

-- https://github.com/PrestaShop/PrestaShop/pull/41643
-- Make category visibility ("Displayed") configurable per shop.
/* PHP:add_column('category_shop', 'active', 'tinyint(1) unsigned NOT NULL DEFAULT \'1\' AFTER `position`'); */;
UPDATE `PREFIX_category_shop` cs INNER JOIN `PREFIX_category` c ON cs.id_category = c.id_category SET cs.active = c.active;
