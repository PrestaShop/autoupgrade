SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/42438
-- Drop the index duplicating the leftmost prefix of the primary key on cart_rule_combination
ALTER TABLE `PREFIX_cart_rule_combination` DROP INDEX `id_cart_rule_1`;
