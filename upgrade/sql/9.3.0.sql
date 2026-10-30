SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/43192
-- Native Security headers feature: the Content Security Policy (storefront and back
-- office) and the static security response headers.
-- Each statement mirrors a fresh-install fixture (db_structure.sql, configuration.xml,
-- feature_flag.xml, tab.xml) so upgrading shops get the same rows a new install gets.
-- Every statement is idempotent (safe to re-run).

-- Storage for collected CSP violation reports (one row per shop + context + directive + source + page).
-- sample/source_file/line_number are informational (the offending inline code and where it lives).
CREATE TABLE IF NOT EXISTS `PREFIX_csp_log` (
  `id_csp_log`   INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_shop`      INT UNSIGNED               NOT NULL,
  `context`      VARCHAR(10)   DEFAULT 'front' NOT NULL,
  `directive`    VARCHAR(64)                NOT NULL,
  `source`       VARCHAR(255)               NOT NULL,
  `document_uri` VARCHAR(255)  DEFAULT ''   NOT NULL,
  `sample`       VARCHAR(64)   DEFAULT NULL,
  `source_file`  VARCHAR(255)  DEFAULT NULL,
  `line_number`  INT UNSIGNED  DEFAULT NULL,
  `hits`         INT UNSIGNED  DEFAULT 1    NOT NULL,
  `date_add`     DATETIME                   NOT NULL,
  `date_upd`     DATETIME                   NOT NULL,
  UNIQUE INDEX `csp_log_shop_directive_source_doc_idx` (`id_shop`, `context`, `directive`, `source`, `document_uri`),
  INDEX `csp_log_shop_prune_idx` (`id_shop`, `context`, `hits`, `date_upd`),
  INDEX `csp_log_shop_id_idx` (`id_shop`, `context`, `id_csp_log`),
  INDEX `csp_log_shop_date_add_idx` (`id_shop`, `context`, `date_add`),
  PRIMARY KEY (`id_csp_log`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Curated allow-list. The context column separates storefront from back-office rules.
CREATE TABLE IF NOT EXISTS `PREFIX_csp_rule` (
  `id_csp_rule`  INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `id_shop`      INT UNSIGNED               NOT NULL,
  `context`      VARCHAR(10)   DEFAULT 'front' NOT NULL,
  `directive`    VARCHAR(64)                NOT NULL,
  `source`       VARCHAR(255)               NOT NULL,
  `date_add`     DATETIME                   NOT NULL,
  UNIQUE INDEX `csp_rule_shop_directive_source_idx` (`id_shop`, `context`, `directive`, `source`),
  PRIMARY KEY (`id_csp_rule`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Module extension hook.
INSERT INTO `PREFIX_hook` (`name`, `title`, `description`)
SELECT 'actionCspPolicyModifier', 'Modify the Content Security Policy', 'This hook is called while the storefront Content Security Policy is being built. Modules receive the mutable policy in the "policy" parameter and may add sources to it (additive only).'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `PREFIX_hook` h WHERE h.`name` = 'actionCspPolicyModifier');

-- Settings, off by default. add_configuration_if_not_exists is idempotent and preserves any merchant
-- override; it also keeps the config inserts off the `SELECT ... WHERE NOT EXISTS` form, which older
-- MySQL (5.x) rejects without a FROM clause.
/* PHP:add_configuration_if_not_exists('PS_CSP_ENABLED', '0'); */;
/* PHP:add_configuration_if_not_exists('PS_CSP_REPORT_ONLY', '1'); */;
/* PHP:add_configuration_if_not_exists('PS_CSP_RETENTION_DAYS', '0'); */;

-- Optional external reporting endpoint (empty = use the built-in collector), per shop.
/* PHP:add_configuration_if_not_exists('PS_CSP_REPORT_URI', ''); */;

-- Back-office (admin) surface settings, global and off by default.
/* PHP:add_configuration_if_not_exists('PS_CSP_ADMIN_ENABLED', '0'); */;
/* PHP:add_configuration_if_not_exists('PS_CSP_ADMIN_REPORT_ONLY', '1'); */;
/* PHP:add_configuration_if_not_exists('PS_CSP_ADMIN_RETENTION_DAYS', '0'); */;

-- Static security headers (non-CSP), all per shop. Every header ships empty/off so enabling the feature
-- flag changes no response header until the merchant opts in; HSTS and Permissions-Policy are off for the
-- same reason and can also break a store if enabled without care.
/* PHP:add_configuration_if_not_exists('PS_SEC_NOSNIFF', ''); */;
/* PHP:add_configuration_if_not_exists('PS_SEC_FRAME_OPTIONS', ''); */;
/* PHP:add_configuration_if_not_exists('PS_SEC_REFERRER_POLICY', ''); */;
/* PHP:add_configuration_if_not_exists('PS_SEC_HSTS', '0'); */;
/* PHP:add_configuration_if_not_exists('PS_SEC_HSTS_MAX_AGE', '15552000'); */;
/* PHP:add_configuration_if_not_exists('PS_SEC_HSTS_SUBDOMAINS', '0'); */;
/* PHP:add_configuration_if_not_exists('PS_SEC_HSTS_PRELOAD', '0'); */;
/* PHP:add_configuration_if_not_exists('PS_SEC_PERMISSIONS_POLICY', ''); */;

-- Back-office static headers are a separate, global set with secure defaults (the storefront keys above
-- stay off/empty). HSTS is per-host, so it is shared from the storefront keys and has no admin variant.
/* PHP:add_configuration_if_not_exists('PS_SEC_ADMIN_NOSNIFF', '1'); */;
/* PHP:add_configuration_if_not_exists('PS_SEC_ADMIN_FRAME_OPTIONS', 'SAMEORIGIN'); */;
/* PHP:add_configuration_if_not_exists('PS_SEC_ADMIN_REFERRER_POLICY', 'strict-origin'); */;
/* PHP:add_configuration_if_not_exists('PS_SEC_ADMIN_PERMISSIONS_POLICY', 'camera=(), microphone=(), geolocation=()'); */;

-- Feature flag (beta, off): the whole feature is gated behind it.
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `state`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `stability`)
SELECT 'csp', 'env,dotenv,db', 0, 'Security headers', 'Admin.Advparameters.Feature', 'Enable / Disable the Security headers page: the Content Security Policy (storefront and back office) and the static security response headers.', 'Admin.Advparameters.Help', 'beta'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `PREFIX_feature_flag` f WHERE f.`name` = 'csp');

-- Back-office tab: fourth child of Advanced Parameters > Security. Ships inactive
-- (active=0); CspFeatureFlagListener sets it active when the flag is turned on.
-- The target table is wrapped in derived tables so it can be referenced while
-- inserting into it (MySQL forbids a direct self-reference in INSERT ... SELECT).
INSERT INTO `PREFIX_tab` (`id_parent`, `position`, `module`, `class_name`, `route_name`, `active`, `enabled`, `icon`, `wording`, `wording_domain`)
SELECT
  (SELECT p.id_tab FROM (SELECT id_tab FROM `PREFIX_tab` WHERE `class_name` = 'AdminParentSecurity') p),
  (SELECT COALESCE(MAX(t.position), 0) + 1 FROM (SELECT `position`, `id_parent` FROM `PREFIX_tab`) t
     WHERE t.id_parent = (SELECT p2.id_tab FROM (SELECT id_tab FROM `PREFIX_tab` WHERE `class_name` = 'AdminParentSecurity') p2)),
  '', 'AdminSecurityHeaders', 'admin_security_headers_index', 0, 1, '', 'Security headers', 'Admin.Navigation.Menu'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM (SELECT `class_name` FROM `PREFIX_tab`) e WHERE e.`class_name` = 'AdminSecurityHeaders');

-- One tab_lang row per installed language.
INSERT INTO `PREFIX_tab_lang` (`id_tab`, `id_lang`, `name`)
SELECT t.id_tab, l.id_lang, 'Security headers'
FROM `PREFIX_tab` t
CROSS JOIN `PREFIX_lang` l
WHERE t.`class_name` = 'AdminSecurityHeaders'
  AND NOT EXISTS (SELECT 1 FROM `PREFIX_tab_lang` tl WHERE tl.id_tab = t.id_tab AND tl.id_lang = l.id_lang);

-- Authorization roles for the tab (CREATE/READ/UPDATE/DELETE) and grant them to
-- the SuperAdmin profile (id_profile = 1), matching Tab::initAccess().
INSERT INTO `PREFIX_authorization_role` (`slug`)
SELECT CONCAT('ROLE_MOD_TAB_ADMINSECURITYHEADERS_', a.action)
FROM (SELECT 'CREATE' AS action UNION SELECT 'READ' UNION SELECT 'UPDATE' UNION SELECT 'DELETE') a
WHERE NOT EXISTS (SELECT 1 FROM `PREFIX_authorization_role` r WHERE r.`slug` = CONCAT('ROLE_MOD_TAB_ADMINSECURITYHEADERS_', a.action));

INSERT INTO `PREFIX_access` (`id_profile`, `id_authorization_role`)
SELECT 1, r.id_authorization_role
FROM `PREFIX_authorization_role` r
WHERE r.`slug` IN (
  'ROLE_MOD_TAB_ADMINSECURITYHEADERS_CREATE',
  'ROLE_MOD_TAB_ADMINSECURITYHEADERS_READ',
  'ROLE_MOD_TAB_ADMINSECURITYHEADERS_UPDATE',
  'ROLE_MOD_TAB_ADMINSECURITYHEADERS_DELETE'
)
AND NOT EXISTS (SELECT 1 FROM `PREFIX_access` ac WHERE ac.id_profile = 1 AND ac.id_authorization_role = r.id_authorization_role);

-- Content Security Policy tab (sibling of "Security headers" under Advanced Parameters > Security).
INSERT INTO `PREFIX_tab` (`id_parent`, `position`, `module`, `class_name`, `route_name`, `active`, `enabled`, `icon`, `wording`, `wording_domain`)
SELECT
  (SELECT p.id_tab FROM (SELECT id_tab FROM `PREFIX_tab` WHERE `class_name` = 'AdminParentSecurity') p),
  (SELECT COALESCE(MAX(t.position), 0) + 1 FROM (SELECT `position`, `id_parent` FROM `PREFIX_tab`) t
     WHERE t.id_parent = (SELECT p2.id_tab FROM (SELECT id_tab FROM `PREFIX_tab` WHERE `class_name` = 'AdminParentSecurity') p2)),
  '', 'AdminSecurityCsp', 'admin_security_csp_index', 0, 1, '', 'Content Security Policy', 'Admin.Navigation.Menu'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM (SELECT `class_name` FROM `PREFIX_tab`) e WHERE e.`class_name` = 'AdminSecurityCsp');

INSERT INTO `PREFIX_tab_lang` (`id_tab`, `id_lang`, `name`)
SELECT t.id_tab, l.id_lang, 'Content Security Policy'
FROM `PREFIX_tab` t
CROSS JOIN `PREFIX_lang` l
WHERE t.`class_name` = 'AdminSecurityCsp'
  AND NOT EXISTS (SELECT 1 FROM `PREFIX_tab_lang` tl WHERE tl.id_tab = t.id_tab AND tl.id_lang = l.id_lang);

INSERT INTO `PREFIX_authorization_role` (`slug`)
SELECT CONCAT('ROLE_MOD_TAB_ADMINSECURITYCSP_', a.action)
FROM (SELECT 'CREATE' AS action UNION SELECT 'READ' UNION SELECT 'UPDATE' UNION SELECT 'DELETE') a
WHERE NOT EXISTS (SELECT 1 FROM `PREFIX_authorization_role` r WHERE r.`slug` = CONCAT('ROLE_MOD_TAB_ADMINSECURITYCSP_', a.action));

INSERT INTO `PREFIX_access` (`id_profile`, `id_authorization_role`)
SELECT 1, r.id_authorization_role
FROM `PREFIX_authorization_role` r
WHERE r.`slug` IN (
  'ROLE_MOD_TAB_ADMINSECURITYCSP_CREATE',
  'ROLE_MOD_TAB_ADMINSECURITYCSP_READ',
  'ROLE_MOD_TAB_ADMINSECURITYCSP_UPDATE',
  'ROLE_MOD_TAB_ADMINSECURITYCSP_DELETE'
)
AND NOT EXISTS (SELECT 1 FROM `PREFIX_access` ac WHERE ac.id_profile = 1 AND ac.id_authorization_role = r.id_authorization_role);

-- Backfill global_settings.csp into the cached theme configuration so a theme.yml that gained the key on
-- upgrade (the Classic theme's 'unsafe-eval') takes effect. Core reads config/themes/<theme>/shop<id>.json
-- in preference to theme.yml and only regenerates it when missing. The cache also holds the merchant's
-- page layouts, so the step patches only the csp key in place and never deletes the file.
/* PHP:ps_930_patch_theme_csp_into_config_cache(); */;
