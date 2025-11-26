SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/39914
INSERT IGNORE INTO `PREFIX_authorization_role` (`slug`) VALUES
  ('ROLE_MOD_TAB_DEFAULT_READ'),
  ('ROLE_MOD_TAB_DEFAULT_CREATE'),
  ('ROLE_MOD_TAB_DEFAULT_UPDATE'),
  ('ROLE_MOD_TAB_DEFAULT_DELETE');
