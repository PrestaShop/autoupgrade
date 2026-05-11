SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- Auto generated hooks added for version 9.1.2
INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'displayModalContent', '', '', '1'),
  (NULL, 'actionPresentCartProduct', '', '', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);
