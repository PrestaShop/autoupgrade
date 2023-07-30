SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

INSERT IGNORE INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionUpdateCartAddress', 'Triggers after changing address on the cart', 'This hook is called after address is changed on the cart', '1'),
  (NULL, 'actionCartGetPackageShippingCost', 'After getting package shipping cost value', 'This hook is called in order to allow to modify package shipping cost', '1');

ALTER TABLE `PREFIX_feature_flag` ADD `type` VARCHAR(64) DEFAULT 'env,dotenv,db' NOT NULL AFTER `name`;

/* Increase size of customized data - https://github.com/PrestaShop/PrestaShop/pull/31109 */
ALTER TABLE `PREFIX_customized_data` MODIFY `value` varchar(1024) NOT NULL;
