SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/* Remove welcome module */
DELETE FROM `PREFIX_tab_lang` WHERE id_tab IN (SELECT id_tab FROM `PREFIX_tab` WHERE class_name = 'AdminWelcome');
DELETE FROM `PREFIX_tab` WHERE class_name = 'AdminWelcome';
DELETE FROM `PREFIX_module_shop` WHERE id_module = (SELECT id_module FROM `PREFIX_module` WHERE `name` = 'welcome');
DELETE FROM `PREFIX_hook_module` WHERE id_module = (SELECT id_module FROM `PREFIX_module` WHERE `name` = 'welcome');
DELETE FROM `PREFIX_module` WHERE `name` = 'welcome';
