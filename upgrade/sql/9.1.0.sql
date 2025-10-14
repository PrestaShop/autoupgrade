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
    'manufacturers', 'suppliers', 'combinations'
) NOT NULL;

/* PHP:add_column('cart_rule_product_rule_group', 'type', 'ENUM("at_least_one_product_rule", "all_product_rules") NOT NULL DEFAULT "at_least_one_product_rule"'); */;

/* Add discount compatibility feature */
/* https://github.com/PrestaShop/PrestaShop/pull/39662 */

/* Create cart_rule_type table for discount types */
CREATE TABLE IF NOT EXISTS `PREFIX_cart_rule_type` (
  `id_cart_rule_type` int(10) unsigned NOT NULL auto_increment,
  `type` varchar(128) NOT NULL,
  `is_core` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_cart_rule_type`),
  UNIQUE KEY `type` (`type`)
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

/* Migrate cart_rule.type column to id_cart_rule_type and populate cart_rule_type table */
/* PHP:ps_910_migrate_cart_rule_types(); */;
