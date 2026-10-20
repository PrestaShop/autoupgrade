SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/43091
INSERT IGNORE INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('multishop', 'env,dotenv,db', 'Multistore', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated multistore pages.', 'Admin.Advparameters.Help', 0, 'beta');
