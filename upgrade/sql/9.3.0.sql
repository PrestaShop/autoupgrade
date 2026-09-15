/* https://github.com/PrestaShop/PrestaShop/pull/41830 */
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `combination_feature_values`, `description_domain`, `state`, `stability`) VALUES
  ('improved_b2b', 'env,dotenv,db', 'Combination feature values', 'Admin.Advparameters.Feature', 'Enable / Disable feature values at combination level, in addition to product level.', 'Admin.Advparameters.Help', 0, 'beta');

/* Association between a feature and a product combination (product_attribute) */
CREATE TABLE `PREFIX_feature_product_attribute` (
  `id_feature` int(10) unsigned NOT NULL,
  `id_product_attribute` int(10) unsigned NOT NULL,
  `id_feature_value` int(10) unsigned NOT NULL,
  PRIMARY KEY (
    `id_feature`, `id_product_attribute`, `id_feature_value`
  ),
  KEY `id_feature_value` (`id_feature_value`),
  KEY `id_product_attribute` (`id_product_attribute`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4  COLLATE=utf8mb4_unicode_ci;
