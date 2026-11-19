SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/42077
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('dashboard', 'env,dotenv,db', 'Dashboard', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated Symfony dashboard page. Native dashboard modules are not yet compatible with the new page and will not display until they are migrated.', 'Admin.Advparameters.Help', 0, 'beta');

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'displayAdminDashboardZoneOne', 'Symfony Dashboard - Zone one', 'Displays module content in the first column of the migrated (Symfony) dashboard page. Modern counterpart of the legacy dashboardZoneOne hook.', '1'),
  (NULL, 'displayAdminDashboardZoneTwo', 'Symfony Dashboard - Zone two', 'Displays module content in the second column of the migrated (Symfony) dashboard page. Modern counterpart of the legacy dashboardZoneTwo hook.', '1'),
  (NULL, 'displayAdminDashboardZoneThree', 'Symfony Dashboard - Zone three', 'Displays module content in the third column of the migrated (Symfony) dashboard page. Modern counterpart of the legacy dashboardZoneThree hook.', '1'),
  (NULL, 'displayAdminDashboardTop', 'Symfony Dashboard - Top', 'Displays module content in the top area of the migrated (Symfony) dashboard page. Modern counterpart of the legacy displayDashboardTop hook.', '1'),
  (NULL, 'displayAdminDashboardBottom', 'Symfony Dashboard - Bottom', 'Displays module content in a full-width area at the bottom of the migrated (Symfony) dashboard page.', '1'),
  (NULL, 'displayAdminDashboardToolbar', 'Symfony Dashboard - Toolbar', 'Displays module content in the toolbar area of the migrated (Symfony) dashboard page. Modern counterpart of the legacy displayDashboardToolbarTopMenu hook.', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);
