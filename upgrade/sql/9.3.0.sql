SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/43132
INSERT IGNORE INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('combination_seo', 'env,dotenv,db', 'Combination SEO', 'Admin.Advparameters.Feature', 'Enable / Disable the meta title, meta description and descriptions at combination level, overriding the product ones.', 'Admin.Advparameters.Help', 0, 'beta');

CREATE TABLE IF NOT EXISTS `PREFIX_product_attribute_content` (
  `id_product_attribute` int(10) unsigned NOT NULL,
  `id_shop` int(10) unsigned NOT NULL,
  `id_lang` int(10) unsigned NOT NULL,
  `description` MEDIUMTEXT,
  `description_short` MEDIUMTEXT,
  `meta_description` varchar(512) DEFAULT NULL,
  `meta_title` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id_product_attribute`, `id_shop`, `id_lang`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- https://github.com/PrestaShop/PrestaShop/pull/43136
/* PHP:add_column('product_attribute_content', 'link_rewrite', 'varchar(128) DEFAULT NULL AFTER `description_short`'); */;
