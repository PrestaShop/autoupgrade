SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/43433
UPDATE `PREFIX_feature_flag` SET `stability` = 'stable' WHERE `name` = 'store';

-- https://github.com/PrestaShop/PrestaShop/pull/43677
INSERT INTO `PREFIX_state` (`id_country`, `id_zone`, `iso_code`, `name`, `tax_behavior`, `active`)
SELECT c.`id_country`, z.`id_zone`, new_state.`iso_code`, new_state.`name`, 0, 1
FROM (
  SELECT 'ID-PD' AS `iso_code`, 'Papua Barat Daya' AS `name`
  UNION ALL SELECT 'ID-PE', 'Papua Pegunungan'
  UNION ALL SELECT 'ID-PS', 'Papua Selatan'
  UNION ALL SELECT 'ID-PT', 'Papua Tengah'
) new_state
  JOIN `PREFIX_country` c ON c.`iso_code` = 'ID'
  JOIN `PREFIX_zone` z ON z.`name` = 'Asia'
WHERE NOT EXISTS (
  SELECT 1 FROM `PREFIX_state` s
  WHERE s.`id_country` = c.`id_country` AND s.`iso_code` = new_state.`iso_code`
);
