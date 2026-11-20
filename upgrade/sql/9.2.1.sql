SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/43452
INSERT IGNORE INTO `PREFIX_hook_alias` (`name`, `alias`) VALUES
  ('actionSupplierGridQueryBuilderModifier', 'actionSuppliersGridQueryBuilderModifier'),
  ('actionSqlRequestGridQueryBuilderModifier', 'actionRequestSqlGridQueryBuilderModifier'),
  ('actionSearchEngineGridQueryBuilderModifier', 'actionSearchEnginesGridQueryBuilderModifier');
