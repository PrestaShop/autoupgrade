/* https://github.com/PrestaShop/PrestaShop/pull/40224 */
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('improved_b2b', 'env,dotenv,db', 'Improved B2B', 'Admin.Advparameters.Feature', 'Enable / Disable the improved B2B mode. To use the feature activate the B2B mode in General Settings', 'Admin.Advparameters.Help', 0, 'beta');

/* https://github.com/PrestaShop/PrestaShop/pull/42077 */
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('dashboard', 'env,dotenv,db', 'Dashboard page', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated Symfony dashboard page. Native dashboard modules are not yet compatible with the new page and will not display until they are migrated.', 'Admin.Advparameters.Help', 0, 'beta');

INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'displayAdminDashboardZoneOne', 'Symfony Dashboard - Zone one', 'Displays module content in the first column of the migrated (Symfony) dashboard page. Modern counterpart of the legacy dashboardZoneOne hook.', '1'),
  (NULL, 'displayAdminDashboardZoneTwo', 'Symfony Dashboard - Zone two', 'Displays module content in the second column of the migrated (Symfony) dashboard page. Modern counterpart of the legacy dashboardZoneTwo hook.', '1'),
  (NULL, 'displayAdminDashboardZoneThree', 'Symfony Dashboard - Zone three', 'Displays module content in the third column of the migrated (Symfony) dashboard page. Modern counterpart of the legacy dashboardZoneThree hook.', '1'),
  (NULL, 'displayAdminDashboardTop', 'Symfony Dashboard - Top', 'Displays module content in the top area of the migrated (Symfony) dashboard page. Modern counterpart of the legacy displayDashboardTop hook.', '1'),
  (NULL, 'displayAdminDashboardBottom', 'Symfony Dashboard - Bottom', 'Displays module content in a full-width area at the bottom of the migrated (Symfony) dashboard page.', '1'),
  (NULL, 'displayAdminDashboardToolbar', 'Symfony Dashboard - Toolbar', 'Displays module content in the toolbar area of the migrated (Symfony) dashboard page. Modern counterpart of the legacy displayDashboardToolbarTopMenu hook.', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

/* https://github.com/PrestaShop/PrestaShop/pull/40632 */
/* Insert B2B foundation */
CREATE TABLE IF NOT EXISTS `PREFIX_business_entity` (
  `id_business_entity` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_shop` INT UNSIGNED NOT NULL,
  `id_customer_group` INT UNSIGNED NOT NULL,
  `external_ref` VARCHAR(255) DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `legal_name` VARCHAR(255) DEFAULT NULL,
  `delivery_authorized` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM ('pending', 'active', 'inactive', 'rejected') NOT NULL DEFAULT 'pending',
  `deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id_business_entity`),
  KEY `business_entity_shop_idx` (`id_shop`),
  KEY `business_entity_customer_group_idx` (`id_customer_group`),
  KEY `business_entity_external_ref_idx` (`external_ref`),
  KEY `business_entity_deleted_idx` (`deleted`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_customer_b2b` (
  `id_customer_b2b` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_customer` INT UNSIGNED NOT NULL,
  `status` ENUM ('pending', 'active', 'rejected') NOT NULL DEFAULT 'pending',
  `external_ref` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  UNIQUE KEY `uniq_customer_b2b_customer` (`id_customer`),
  PRIMARY KEY (`id_customer_b2b`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_business_entity_customer_b2b` (
  `id_business_entity_customer_b2b` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_business_entity` INT UNSIGNED NOT NULL,
  `id_customer_b2b` INT UNSIGNED NOT NULL,
  `id_role` INT UNSIGNED NOT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id_business_entity_customer_b2b`),
  UNIQUE KEY `uniq_be_customer` (`id_business_entity`, `id_customer_b2b`),
  KEY `business_entity_customer_b2b_be_idx` (`id_business_entity`),
  KEY `business_entity_customer_b2b_customer_idx` (`id_customer_b2b`),
  KEY `business_entity_customer_b2b_role_idx` (`id_role`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_business_entity_identifier` (
  `id_identifier` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_business_entity` INT UNSIGNED NOT NULL,
  `id_business_identifier` INT UNSIGNED NOT NULL,
  `value` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id_identifier`),
  UNIQUE KEY `uniq_business_entity_identifier` (`id_business_entity`, `id_business_identifier`),
  KEY `business_entity_identifier_id_business_entity_idx` (`id_business_entity`),
  KEY `business_entity_identifier_id_business_identifier_idx` (`id_business_identifier`),
  KEY `business_entity_identifier_value_idx` (`value`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_business_identifier` (
  `id_business_identifier` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `label` VARCHAR(255) NOT NULL,
  `unremovable` TINYINT(1) NOT NULL DEFAULT 0,
  `id_zone` INT UNSIGNED DEFAULT NULL,
  `deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id_business_identifier`),
  KEY `business_identifier_zone_idx` (`id_zone`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_business_entity_address` (
  `id_business_entity_address` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_business_entity` INT UNSIGNED NOT NULL,
  `id_address` INT UNSIGNED NOT NULL,
  `address_type` ENUM ('both', 'invoice', 'delivery') NOT NULL DEFAULT 'both',
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id_business_entity_address`),
  UNIQUE KEY `uniq_be_address` (`id_business_entity`, `id_address`, `address_type`),
  KEY `business_entity_address_be_idx` (`id_business_entity`),
  KEY `business_entity_address_address_idx` (`id_address`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_b2b_role` (
  `id_role` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `role` VARCHAR(64) NOT NULL,
  UNIQUE KEY `uniq_b2b_role` (`role`),
  PRIMARY KEY (`id_role`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `PREFIX_b2b_role_authorization_role` (
  `id_role` INT UNSIGNED NOT NULL,
  `id_authorization_role` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id_role`, `id_authorization_role`),
  KEY `b2b_role_authorization_role_role_idx` (`id_role`),
  KEY `b2b_role_authorization_role_auth_role_idx` (`id_authorization_role`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- https://github.com/PrestaShop/PrestaShop/pull/41028
/* PHP:ps_920_business_entities_tabs(); */;
