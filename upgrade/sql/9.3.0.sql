SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/41911
-- Import page migration: feature flag (disabled, beta)
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('import', 'env,dotenv,db', 'Import', 'Admin.Advparameters.Feature', 'Enable / Disable the migrated import page.', 'Admin.Advparameters.Help', 0, 'beta');

-- Import jobs, tracked across batches
CREATE TABLE IF NOT EXISTS `PREFIX_import_job` (
  `import_job_uuid` char(36) COLLATE utf8mb4_bin NOT NULL,
  `entity_type` varchar(64) NOT NULL,
  `id_shop` int(10) UNSIGNED NOT NULL,
  `status` varchar(32) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `skip_rows` int(10) NOT NULL DEFAULT 0,
  `data_record_count` int(10) NOT NULL DEFAULT 0,
  `current_phase_id` varchar(64) DEFAULT NULL,
  `current_offset` int(10) NOT NULL DEFAULT 0,
  `resume_cursor` varchar(255) DEFAULT NULL,
  `skipped_row_count` int(10) NOT NULL DEFAULT 0,
  `phase_totals` JSON NOT NULL,
  `skipped_rows` JSON NOT NULL,
  `messages` JSON NOT NULL,
  `context` JSON NOT NULL,
  `options` JSON NOT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`import_job_uuid`),
  KEY `date_upd` (`date_upd`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
