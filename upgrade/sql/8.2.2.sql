SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionDuplicateCartData','Cart Duplication','This hook is triggered after all the cart related data has been duplicated', '1'),
  (NULL, 'displayCartExtraProductInfo','Extra information in shopping cart product line','This hook adds extra information to the product lines, in the shopping cart', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

ALTER TABLE `PREFIX_product_shop` ADD INDEX `shop_tax` (`id_shop`, `id_tax_rules_group`);