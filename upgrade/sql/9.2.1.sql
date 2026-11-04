SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/43327
-- Remove orphaned feature value translations.
DELETE fvl
FROM `PREFIX_feature_value_lang` fvl
LEFT JOIN `PREFIX_feature_value` fv ON fv.`id_feature_value` = fvl.`id_feature_value`
WHERE fv.`id_feature_value` IS NULL;
