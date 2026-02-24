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
  ('tag', 'env,dotenv,db', 'Tag', 'Admin.Advparameters.Feature', 'Enable / Disable the tag page.', 'Admin.Advparameters.Help', 0, 'beta'),
  ('improved_b2b', 'env,dotenv,db', 'Improved B2B', 'Admin.Advparameters.Feature', 'Enable / Disable the improved B2B mode. To use the feature activate the B2B mode in General Settings', 'Admin.Advparameters.Help', 0, 'beta');

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
  (NULL, 'actionDiscountGridPresenterModifier', 'Modify discount grid template data', 'This hook allows to modify data which is about to be used in template for discount grid', '1'),
  -- https://github.com/PrestaShop/PrestaShop/pull/40720
  (NULL, 'actionUpdateDefaultCombinationAfter', 'After default combination update', 'Allows modules to react after the default combination of a product has been updated. This hook is triggered once the default combination has been successfully changed.', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

/* Discount types for compatibility */
CREATE TABLE IF NOT EXISTS `PREFIX_cart_rule_type` (
  `id_cart_rule_type` int(10) unsigned NOT NULL auto_increment,
  `discount_type` varchar(128) NOT NULL,
  `is_core` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_cart_rule_type`),
  UNIQUE KEY `discount_type` (`discount_type`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Localized names for cart rule types */
CREATE TABLE IF NOT EXISTS `PREFIX_cart_rule_type_lang` (
  `id_cart_rule_type` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `name` varchar(254) NOT NULL,
  `description` TEXT,
  PRIMARY KEY (`id_cart_rule_type`, `id_lang`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Cart rule compatibility table */
CREATE TABLE IF NOT EXISTS `PREFIX_cart_rule_compatible_types` (
  `id_cart_rule` int(10) unsigned NOT NULL,
  `id_cart_rule_type` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_cart_rule`, `id_cart_rule_type`),
  KEY `id_cart_rule` (`id_cart_rule`),
  KEY `id_cart_rule_type` (`id_cart_rule_type`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* PHP:add_column('cart_rule', 'id_cart_rule_type', 'INT UNSIGNED DEFAULT NULL'); */;
/* PHP:add_column('cart_rule', 'minimum_product_quantity', 'INT UNSIGNED NOT NULL DEFAULT 0'); */;
/* PHP:add_index_if_not_exists('cart_rule', 'id_cart_rule_type', '(`id_cart_rule_type`)'); */;

INSERT INTO `PREFIX_cart_rule_type` (`id_cart_rule_type`, `discount_type`, `is_core`, `active`, `date_add`, `date_upd`) VALUES
  (NULL, 'free_shipping', '1', '1', NOW(), NOW()),
  (NULL, 'cart_level', '1', '1', NOW(), NOW()),
  (NULL, 'order_level', '1', '1', NOW(), NOW()),
  (NULL, 'product_level', '1', '1', NOW(), NOW()),
  (NULL, 'free_gift', '1', '1', NOW(), NOW())
ON DUPLICATE KEY UPDATE `discount_type` = VALUES(`discount_type`), `is_core` = VALUES(`is_core`), `active` = VALUES(`active`);

/* PHP:ps_910_init_cart_rule_type_lang_translations(); */;

/* Make quantity and quantity_per_user nullable in cart_rule table */
/* https://github.com/PrestaShop/PrestaShop/pull/40330 */
/* PHP:add_column('cart_rule', 'quantity', 'int(10) unsigned DEFAULT \'0\''); */;
/* PHP:add_column('cart_rule', 'quantity_per_user', 'int(10) unsigned DEFAULT \'0\''); */;

/* New hooks implemented in https://github.com/PrestaShop/PrestaShop/pull/40730 */
INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionOverrideShippingFreePrice', 'Override price that determines free shipping', 'Allows modules to override the free shipping price and return their custom value, for example to specify it by zone or other criteria.', '1'),
  (NULL, 'actionOverrideShippingFreeWeight', 'Override weight that determines free shipping', 'Allows modules to override the free shipping weight and return their custom value, for example to specify it by zone or other criteria.', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

/* Insert B2B foundation */
/* https://github.com/PrestaShop/PrestaShop/pull/40632 */
CREATE TABLE `PREFIX_business_entity`
(
    `id_business_entity`       INT UNSIGNED AUTO_INCREMENT                     NOT NULL,
    `enterprise_id`            VARCHAR(255) NOT NULL,
    `external_ref`             VARCHAR(255) DEFAULT NULL,
    `name`                     VARCHAR(255) NOT NULL,
    `legal_name`               VARCHAR(255) DEFAULT NULL,
    `flag_delivery_authorized` TINYINT(1)                                      NOT NULL DEFAULT 0,
    `status`                   ENUM ('pending','active','inactive','rejected') NOT NULL DEFAULT 'pending',
    `created_at`               DATETIME     NOT NULL,
    `updated_at`               DATETIME     NOT NULL,
    INDEX                      `business_entity_enterprise_id_idx` (`enterprise_id`),
    INDEX                      `business_entity_external_ref_idx` (`external_ref`),
    PRIMARY KEY (`id_business_entity`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PREFIX_customer_b2b`
(
    `id_customer_b2b` INT UNSIGNED AUTO_INCREMENT         NOT NULL,
    `id_customer`     INT UNSIGNED                        NOT NULL,
    `status`          ENUM ('pending','active','refused') NOT NULL DEFAULT 'pending',
    `external_ref`    VARCHAR(255) DEFAULT NULL,
    `created_at`      DATETIME NOT NULL,
    `updated_at`      DATETIME NOT NULL,
    UNIQUE INDEX `uniq_customer_b2b_customer` (`id_customer`),
    PRIMARY KEY (`id_customer_b2b`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PREFIX_business_entity_customer_b2b`
(
    `id_business_entity_customer_b2b` INT UNSIGNED AUTO_INCREMENT NOT NULL,
    `id_business_entity`              INT UNSIGNED                NOT NULL,
    `id_customer_b2b`                 INT UNSIGNED                NOT NULL,
    `id_role_b2b`                     INT UNSIGNED                NOT NULL,
    `is_default`                      TINYINT(1)                  NOT NULL DEFAULT 0,
    `created_at`                      DATETIME NOT NULL,
    UNIQUE INDEX `uniq_be_customer` (`id_business_entity`, `id_customer_b2b`),
    INDEX                             `business_entity_customer_b2b_be_idx` (`id_business_entity`),
    INDEX                             `business_entity_customer_b2b_customer_idx` (`id_customer_b2b`),
    INDEX                             `business_entity_customer_b2b_role_idx` (`id_role_b2b`),
    PRIMARY KEY (`id_business_entity_customer_b2b`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PREFIX_business_entity_identifier`
(
    `id_identifier`                  INT UNSIGNED AUTO_INCREMENT    NOT NULL,
    `id_business_entity`         INT UNSIGNED                       NOT NULL,
    `id_business_identifier`     INT UNSIGNED                       NOT NULL,
    `value`              VARCHAR(255) NOT NULL,
    UNIQUE INDEX `uniq_business_entity_identifier` (`id_business_entity`, `id_business_identifier`),
    INDEX                `business_entity_identifier_id_business_entity_idx` (`id_business_entity`),
    INDEX                `business_entity_identifier_id_business_identifier_idx` (`id_business_identifier`),
    INDEX                `business_entity_identifier_value_idx` (`value`),
    PRIMARY KEY (`id_identifier`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PREFIX_business_identifier`
(
    `id_business_identifier` INT UNSIGNED AUTO_INCREMENT        NOT NULL,
    `unremovable`    TINYINT(1)                                 NOT NULL DEFAULT 0,
    `deleted`        TINYINT(1)                                 NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_business_identifier`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PREFIX_business_entity_address`
(
    `id_business_entity` INT UNSIGNED                       NOT NULL,
    `id_address`         INT UNSIGNED                       NOT NULL,
    `address_type`       ENUM ('both','invoice','delivery') NOT NULL DEFAULT 'both',
    PRIMARY KEY (`id_business_entity`, `id_address`),
    INDEX                `business_entity_address_address_idx` (`id_address`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PREFIX_b2b_role`
(
    `id_role` INT UNSIGNED AUTO_INCREMENT NOT NULL,
    `role`    VARCHAR(64) NOT NULL,
    UNIQUE INDEX `uniq_b2b_role` (`role`),
    PRIMARY KEY (`id_role`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PREFIX_b2b_role_authorization_role`
(
    `id_role`               INT UNSIGNED NOT NULL,
    `id_authorization_role` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id_role`, `id_authorization_role`),
    INDEX                   `b2b_role_authorization_role_role_idx` (`id_role`),
    INDEX                   `b2b_role_authorization_role_auth_role_idx` (`id_authorization_role`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;