SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/*
    Add default configuration for one-page checkout
    https://github.com/PrestaShop/PrestaShop/pull/40864
*/
/* PHP:add_configuration_if_not_exists('PS_ONE_PAGE_CHECKOUT_ENABLED', '0'); */;

/*
    Add default flag to manage the different types of delivery addresses
    https://github.com/PrestaShop/PrestaShop/pull/40891
*/
ALTER TABLE `PREFIX_business_entity_address`
    ADD COLUMN `default` TINYINT(1) NOT NULL DEFAULT 0 AFTER `address_type`;