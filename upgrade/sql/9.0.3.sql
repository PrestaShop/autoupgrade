SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

  -- https://github.com/PrestaShop/PrestaShop/pull/40403
INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionFrontControllerDetectContextCountryAfter','Action after detecting context country','Allows modules to modify the context country after it has been detected via geolocation.', '1'),
  (NULL, 'actionFrontControllerInitContextCurrencyAfter','Action after initializing context currency','Allows modules to modify the context currency after it has been initialized.', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);
