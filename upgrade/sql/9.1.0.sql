SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/* Insert new feature flags introduced for the newly improved shipment system */
/* https://github.com/PrestaShop/PrestaShop/pull/38040 */
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('improved_shipment', 'env,dotenv,db', 'Improved shipment', 'Admin.Advparameters.Feature', 'Enable / Disable the newly improved shipment system', 'Admin.Advparameters.Help', 0, 'beta');
