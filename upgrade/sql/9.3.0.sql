SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/43025
-- Index carrier_lang on id_carrier so that loading a carrier without language does not scan the whole table
/* PHP:add_index_if_not_exists('carrier_lang', 'id_carrier', '(`id_carrier`, `id_shop`)'); */;
