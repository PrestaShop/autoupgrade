SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/*
  Insert new feature flags introduced for the one-page checkout
  https://github.com/PrestaShop/PrestaShop/pull/40796
*/
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('one_page_checkout', 'env,dotenv,db', 'One-page checkout', 'Admin.Advparameters.Feature', 'Enable / Disable one-page checkout flow. All checkout steps (address, delivery, payment) are grouped on a single page to reduce friction and improve conversion rate.', 'Admin.Advparameters.Help', 0, 'beta');

