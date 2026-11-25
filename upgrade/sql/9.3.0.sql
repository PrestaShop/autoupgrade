SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/43433
UPDATE `PREFIX_feature_flag` SET `stability` = 'stable' WHERE `name` = 'store';

-- https://github.com/PrestaShop/PrestaShop/pull/43643
-- Indexation by search engines of a category
/* PHP:add_column('category', 'indexation', 'tinyint(1) unsigned NOT NULL DEFAULT \'1\' AFTER `id_type_redirected`'); */;

-- Noindex of the front office pages whose URL holds one of the listed parameters.
-- The default list is the former hard-coded rule of product listings.
INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`)
SELECT 'PS_NOINDEX_URL_PARAMETERS', '1', NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `PREFIX_configuration` WHERE `name` = 'PS_NOINDEX_URL_PARAMETERS');

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`)
SELECT 'PS_NOINDEX_URL_PARAMETERS_LIST', 'q,order', NOW(), NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `PREFIX_configuration` WHERE `name` = 'PS_NOINDEX_URL_PARAMETERS_LIST');

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionFrontControllerSetPageVariables', 'Modify the page variables of a Front Office page', 'Allows modifying the page variables (meta title, meta description, robots, canonical URL, body classes) once the controller has built them, before they are used by the templates and the structured data.', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);
