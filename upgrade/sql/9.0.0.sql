SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

INSERT IGNORE INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionCartGetPackageShippingCost', 'After getting package shipping cost value', 'This hook is called in order to allow to modify package shipping cost', '1');