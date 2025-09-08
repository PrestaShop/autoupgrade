SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  -- https://github.com/PrestaShop/PrestaShop/pull/39366
  (NULL, 'actionCheckoutStepRenderTemplate','Modify the parameters of the checkout step template rendering','This hook is called when rendering every checkout step template', '1'),
  -- https://github.com/PrestaShop/PrestaShop/pull/39277
  (NULL, 'actionModifyHtmlPurifierConfig', 'Called when configuring HTMLPurifier', 'Allows modules to modify the HTMLPurifier definition by adding custom allowed HTML elements or attributes during Tools::purifyHTML().', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
  ('PS_MIN_LOGGER_LEVEL_IN_DB', '1', NOW(), NOW())
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
