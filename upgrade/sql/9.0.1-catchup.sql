-- PrestaShop 9.0.0 missing changes

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  -- https://github.com/PrestaShop/PrestaShop/pull/34133
  (NULL, 'actionSubmitAccountBefore', 'Before customer account creation', 'This hook is called before a customer account creation', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

-- https://github.com/PrestaShop/PrestaShop/pull/37861
/* PHP:add_index_if_not_exists('customer_message', 'id_product', '(`id_product`)'); */;
