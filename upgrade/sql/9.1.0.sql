SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/* Insert new feature flags introduced for the newly improved shipment system */
/* https://github.com/PrestaShop/PrestaShop/pull/38040 */
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('improved_shipment', 'env,dotenv,db', 'Improved shipment', 'Admin.Advparameters.Feature', 'Enable / Disable the newly improved shipment system', 'Admin.Advparameters.Help', 0, 'beta'),
  ('discount', 'env,dotenv,db', 'Discount', 'Admin.Advparameters.Feature', 'Enable / Disable the new discount system.', 'Admin.Advparameters.Help', 0, 'beta');

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
