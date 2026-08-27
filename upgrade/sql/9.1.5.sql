SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/41824
INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionNotFound', 'Action when a page is not found', 'Allows modules to react when a page is not found - log it, redirect or perform other actions.', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);
