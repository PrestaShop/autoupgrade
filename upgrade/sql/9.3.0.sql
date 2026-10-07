SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/43135
/* PHP:add_column('product_attribute', 'active', 'tinyint(1) unsigned NOT NULL DEFAULT \'1\' AFTER `available_date`'); */;
/* PHP:add_column('product_attribute_shop', 'active', 'tinyint(1) unsigned NOT NULL DEFAULT \'1\' AFTER `available_date`'); */;
INSERT IGNORE INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('combination_status', 'env,dotenv,db', 'Combination status', 'Admin.Advparameters.Feature', 'Enable / Disable the status of combinations: a disabled combination stays in the back office but cannot be ordered.', 'Admin.Advparameters.Help', 0, 'beta');
