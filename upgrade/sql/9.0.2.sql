SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/39606
ALTER TABLE `PREFIX_customer_message`
    MODIFY `user_agent` varchar(255) DEFAULT NULL;

-- https://github.com/PrestaShop/PrestaShop/pull/39914
INSERT IGNORE INTO `PREFIX_authorization_role` (`slug`) VALUES
  ('ROLE_MOD_TAB_DEFAULT_READ'),
  ('ROLE_MOD_TAB_DEFAULT_CREATE'),
  ('ROLE_MOD_TAB_DEFAULT_UPDATE'),
  ('ROLE_MOD_TAB_DEFAULT_DELETE');

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  -- https://github.com/PrestaShop/PrestaShop/pull/39913
  (NULL, 'actionOverrideQuantityAvailableByProduct','Override available quantity by product','Allows modules to override the available quantity returned by StockAvailable::getQuantityAvailableByProduct().', '1'),
  (NULL, 'actionCheckAttributeQuantity','Check product attribute quantity availability','Allows modules to validate or override the stock availability check for a specific product combination.', '1'),
  (NULL, 'actionOverrideProductQuantity','Override product quantity calculation','Allows modules to override the final product quantity returned by Product::getQuantity(), including cart-aware calculations.', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);
