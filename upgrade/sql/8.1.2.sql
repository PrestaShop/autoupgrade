SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/*
Security section tabs were correctly added (ps_800_add_security_tab.php) for people coming from 1.7.8,
but had missing wordings on new 8.0.0-8.1.1 installs.
We fixed it for people installing fresh 8.1.2, but we also need to fix it for people that started on 8.0.0-8.1.1 versions.
*/
UPDATE `PREFIX_tab` SET wording_domain = 'Admin.Navigation.Menu', wording = 'Security' WHERE class_name = 'AdminParentSecurity';
UPDATE `PREFIX_tab` SET wording_domain = 'Admin.Navigation.Menu', wording = 'Employee Sessions' WHERE class_name = 'AdminSecuritySessionEmployee';
UPDATE `PREFIX_tab` SET wording_domain = 'Admin.Navigation.Menu', wording = 'Customer Sessions' WHERE class_name = 'AdminSecuritySessionCustomer';

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionLanguageLinkParameters', 'Add parameters to language link', 'Allows modules to provide proper parameters for links in other languages.', '1'),
  (NULL, 'actionAfterLoadRoutes', 'Triggers after loading routes', 'Allow modules to modify routes in any way or add their own multilanguage routes.', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);
