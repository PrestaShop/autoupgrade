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

