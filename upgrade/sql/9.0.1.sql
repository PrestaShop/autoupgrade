SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  -- https://github.com/PrestaShop/PrestaShop/pull/39366
  (NULL, 'actionCheckoutStepRenderTemplate','Modify the parameters of the checkout step template rendering','This hook is called when rendering every checkout step template', '1'),
  -- https://github.com/PrestaShop/PrestaShop/pull/39277
  (NULL, 'actionModifyHtmlPurifierConfig', 'Called when configuring HTMLPurifier', 'Allows modules to modify the HTMLPurifier definition by adding custom allowed HTML elements or attributes during Tools::purifyHTML().', '1'),
  -- https://github.com/PrestaShop/PrestaShop/pull/38487
  (NULL, 'actionGetPdfTemplateObject', 'Get PDF template object', 'This hook allows to recieve a PDF template object from modules', '1'),
  -- https://github.com/PrestaShop/PrestaShop/pull/39716
  (NULL, 'additionalHtmlAttributesFormFields', '', '', '1'),
  (NULL, 'actionGetCartRuleContextualValue', '', '', '1'),
  (NULL, 'actionApplyCartRule', '', '', '1'),
  (NULL, 'actionDatabaseLogsForm', 'Modify database logs options form content', 'This hook allows to modify database logs options form FormBuilder', '1'),
  (NULL, 'actionDatabaseLogsSave', 'Modify database logs options form saved data', 'This hook allows to modify data of database logs options form after it was saved', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

INSERT INTO `PREFIX_configuration` (`name`, `value`, `date_add`, `date_upd`) VALUES
  ('PS_MIN_LOGGER_LEVEL_IN_DB', '1', NOW(), NOW())
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

/* Remove theme_name field from image_type table */
/* https://github.com/PrestaShop/PrestaShop/pull/39554 */
/* PHP:ps_901_set_unicity_on_image_type(); */;
ALTER TABLE `PREFIX_image_type`
    DROP COLUMN `theme_name`,
    DROP INDEX `UNIQ_907C95215E237E0614E48A3B`,
    ADD UNIQUE KEY `UNIQ_907C95215E237E06` (`name`);

-- https://github.com/PrestaShop/PrestaShop/pull/39012
UPDATE `PREFIX_state` s
  JOIN `PREFIX_country` c ON s.id_country = c.id_country
  SET s.name = 'Valle d\'Aosta'
WHERE s.iso_code = 'AO' AND c.iso_code = 'IT';

UPDATE `PREFIX_state` s
  JOIN `PREFIX_country` c ON s.id_country = c.id_country
  SET s.name = 'Massa-Carrara' WHERE s.iso_code = 'MS' AND c.iso_code = 'IT';

UPDATE `PREFIX_state` s
  JOIN `PREFIX_country` c ON s.id_country = c.id_country
  SET s.name = 'Monza e Brianza'
WHERE s.iso_code = 'MB' AND c.iso_code = 'IT';

UPDATE `PREFIX_state` s
  JOIN `PREFIX_country` c ON s.id_country = c.id_country
  SET s.name = 'Pesaro e Urbino'
WHERE s.iso_code = 'PU' AND c.iso_code = 'IT';

INSERT INTO `PREFIX_state` (id_country, id_zone, iso_code, name, active)
SELECT 
  c.id_country, z.id_zone, 'SU' AS iso_code, 'Sulcis Iglesiente' AS name, 1 AS active
FROM `PREFIX_country` c
  JOIN `PREFIX_zone` z ON z.name = 'Europe'
WHERE c.iso_code = 'IT';
