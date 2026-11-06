SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/43433
UPDATE `PREFIX_feature_flag` SET `stability` = 'stable' WHERE `name` = 'store';
