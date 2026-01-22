SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/* Auto generated hooks added for version 9.0.3 */
INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionFrontControllerDetectContextCountryAfter', 'Action after detecting context country', 'Allows modules to modify the context country after it has been detected via geolocation.', '1'),
  (NULL, 'actionFrontControllerInitContextCurrencyAfter', 'Action after initializing context currency', 'Allows modules to modify the context currency after it has been initialized.', '1'),
  (NULL, 'actionFacetedSearchSetSupportedControllers', '', '', '1'),
  (NULL, 'actionFacetedSearchFilters', '', '', '1'),
  (NULL, 'actionMainMenuModifier', '', '', '1'),
  (NULL, 'actionFacetedSearchCacheKeyGeneration', '', '', '1'),
  (NULL, 'gSitemapAppendUrls', '', '', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);
