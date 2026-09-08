SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/42162
-- Drop the unused id_country index on address. No query reads it - every core reference joins
-- country on its primary key - and it misleads the optimizer on the orders grid into driving from
-- country_lang instead of orders, turning a 50 row page into a scan with a filesort.
/* PHP:ps_915_drop_address_country_index(); */;
