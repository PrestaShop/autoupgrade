SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/39277
-- Add a new hook to allow modules to modify the HTMLPurifier configuration
INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionModifyHtmlPurifierConfig', 'Called when configuring HTMLPurifier', 'Allows modules to modify the HTMLPurifier definition by adding custom allowed HTML elements or attributes during Tools::purifyHTML().', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

-- https://github.com/PrestaShop/PrestaShop/pull/39442
-- Add the configuration value PS_MIN_LOGGER_LEVEL_IN_DB (default value = 1)
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
  ('PS_MIN_LOGGER_LEVEL_IN_DB', '1', NOW(), NOW())
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);