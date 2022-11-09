SET SESSION sql_mode='';
SET NAMES 'utf8';

DELETE FROM `PREFIX_configuration` WHERE `name` = 'PS_SMARTY_CACHING_TYPE';
UPDATE `PREFIX_translation` set theme = null WHERE `theme` = 'classic';
