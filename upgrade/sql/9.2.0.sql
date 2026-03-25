/* https://github.com/PrestaShop/PrestaShop/pull/40224 */
INSERT INTO `PREFIX_feature_flag` (`name`, `type`, `label_wording`, `label_domain`, `description_wording`, `description_domain`, `state`, `stability`) VALUES
  ('improved_b2b', 'env,dotenv,db', 'Improved B2B', 'Admin.Advparameters.Feature', 'Enable / Disable the improved B2B mode. To use the feature activate the B2B mode in General Settings', 'Admin.Advparameters.Help', 0, 'beta');

/* Insert B2B foundation */
/* https://github.com/PrestaShop/PrestaShop/pull/40632 */
CREATE TABLE `PREFIX_business_entity`
(
    `id_business_entity`       INT UNSIGNED AUTO_INCREMENT                     NOT NULL,
    `enterprise_id`            VARCHAR(255) NOT NULL,
    `external_ref`             VARCHAR(255) DEFAULT NULL,
    `name`                     VARCHAR(255) NOT NULL,
    `legal_name`               VARCHAR(255) DEFAULT NULL,
    `flag_delivery_authorized` TINYINT(1)                                      NOT NULL DEFAULT 0,
    `status`                   ENUM ('pending','active','inactive','rejected') NOT NULL DEFAULT 'pending',
    `created_at`               DATETIME     NOT NULL,
    `updated_at`               DATETIME     NOT NULL,
    INDEX                      `business_entity_enterprise_id_idx` (`enterprise_id`),
    INDEX                      `business_entity_external_ref_idx` (`external_ref`),
    PRIMARY KEY (`id_business_entity`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PREFIX_customer_b2b`
(
    `id_customer_b2b` INT UNSIGNED AUTO_INCREMENT         NOT NULL,
    `id_customer`     INT UNSIGNED                        NOT NULL,
    `status`          ENUM ('pending','active','refused') NOT NULL DEFAULT 'pending',
    `external_ref`    VARCHAR(255) DEFAULT NULL,
    `created_at`      DATETIME NOT NULL,
    `updated_at`      DATETIME NOT NULL,
    UNIQUE INDEX `uniq_customer_b2b_customer` (`id_customer`),
    PRIMARY KEY (`id_customer_b2b`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PREFIX_business_entity_customer_b2b`
(
    `id_business_entity_customer_b2b` INT UNSIGNED AUTO_INCREMENT NOT NULL,
    `id_business_entity`              INT UNSIGNED                NOT NULL,
    `id_customer_b2b`                 INT UNSIGNED                NOT NULL,
    `id_role_b2b`                     INT UNSIGNED                NOT NULL,
    `is_default`                      TINYINT(1)                  NOT NULL DEFAULT 0,
    `created_at`                      DATETIME NOT NULL,
    UNIQUE INDEX `uniq_be_customer` (`id_business_entity`, `id_customer_b2b`),
    INDEX                             `business_entity_customer_b2b_be_idx` (`id_business_entity`),
    INDEX                             `business_entity_customer_b2b_customer_idx` (`id_customer_b2b`),
    INDEX                             `business_entity_customer_b2b_role_idx` (`id_role_b2b`),
    PRIMARY KEY (`id_business_entity_customer_b2b`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PREFIX_business_entity_identifier`
(
    `id_identifier`                  INT UNSIGNED AUTO_INCREMENT    NOT NULL,
    `id_business_entity`         INT UNSIGNED                       NOT NULL,
    `id_business_identifier`     INT UNSIGNED                       NOT NULL,
    `value`              VARCHAR(255) NOT NULL,
    UNIQUE INDEX `uniq_business_entity_identifier` (`id_business_entity`, `id_business_identifier`),
    INDEX                `business_entity_identifier_id_business_entity_idx` (`id_business_entity`),
    INDEX                `business_entity_identifier_id_business_identifier_idx` (`id_business_identifier`),
    INDEX                `business_entity_identifier_value_idx` (`value`),
    PRIMARY KEY (`id_identifier`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PREFIX_business_identifier`
(
    `id_business_identifier` INT UNSIGNED AUTO_INCREMENT        NOT NULL,
    `unremovable`    TINYINT(1)                                 NOT NULL DEFAULT 0,
    `deleted`        TINYINT(1)                                 NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_business_identifier`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PREFIX_business_entity_address`
(
    `id_business_entity` INT UNSIGNED                       NOT NULL,
    `id_address`         INT UNSIGNED                       NOT NULL,
    `address_type`       ENUM ('both','invoice','delivery') NOT NULL DEFAULT 'both',
    `default`            TINYINT(1)                         NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_business_entity`, `id_address`),
    INDEX                `business_entity_address_address_idx` (`id_address`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PREFIX_b2b_role`
(
    `id_role` INT UNSIGNED AUTO_INCREMENT NOT NULL,
    `role`    VARCHAR(64) NOT NULL,
    UNIQUE INDEX `uniq_b2b_role` (`role`),
    PRIMARY KEY (`id_role`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PREFIX_b2b_role_authorization_role`
(
    `id_role`               INT UNSIGNED NOT NULL,
    `id_authorization_role` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id_role`, `id_authorization_role`),
    INDEX                   `b2b_role_authorization_role_role_idx` (`id_role`),
    INDEX                   `b2b_role_authorization_role_auth_role_idx` (`id_authorization_role`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- https://github.com/PrestaShop/PrestaShop/pull/41028
/* PHP:ps_920_business_entities_tabs(); */;
