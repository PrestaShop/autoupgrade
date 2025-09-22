SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/39606
ALTER TABLE `PREFIX_customer_message`
    MODIFY `user_agent` varchar(255) NOT NULL;

-- https://github.com/PrestaShop/PrestaShop/pull/39914
INSERT IGNORE INTO `PREFIX_authorization_role` (`slug`) VALUES
  ('ROLE_MOD_TAB_DEFAULT_READ'),
  ('ROLE_MOD_TAB_DEFAULT_CREATE'),
  ('ROLE_MOD_TAB_DEFAULT_UPDATE'),
  ('ROLE_MOD_TAB_DEFAULT_DELETE');
