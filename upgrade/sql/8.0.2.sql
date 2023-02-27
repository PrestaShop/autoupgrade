SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

INSERT IGNORE INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionJsAfterModuleImport', 'Add JS after module import', 'This hook allows to add a JS function to execute after a module import. This will help to perform actions after module upgrade in a different process', '1')
;
