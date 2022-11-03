SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

UPDATE `PREFIX_configuration` SET `value` = 'US/Pacific' WHERE `name` = 'PS_TIMEZONE' AND `value` = 'US/Pacific-New' LIMIT 1;
DELETE FROM `PREFIX_timezone` WHERE `name` = 'US/Pacific-New';

/* New hooks added for version 8.1.0 */
INSERT IGNORE INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionProductPriceCalculation', 'Product price calculation', 'This hook allows you to override or change the result of low-level price calculation', '1'),
;
