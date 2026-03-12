ALTER TABLE `PREFIX_business_entity_address`
    ADD COLUMN `default` TINYINT(1) NOT NULL DEFAULT 0 AFTER `address_type`;
