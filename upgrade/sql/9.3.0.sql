-- https://github.com/PrestaShop/PrestaShop/pull/41565
/* Migrated Search configuration page (Symfony): add the search_page feature flag (beta) */
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('search_page', 'env,dotenv,db', 'Search', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated Search (Configure > Shop Parameters > Search) page.', 'Admin.Advparameters.Help', 0, 'beta');
