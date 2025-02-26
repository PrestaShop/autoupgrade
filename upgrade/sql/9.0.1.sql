SET
    SESSION sql_mode = '';

SET
    NAMES 'utf8mb4';

/* Insert new shipment table */
/* https://github.com/PrestaShop/PrestaShop/pull/38046 */
CREATE TABLE IF NOT EXISTS `PREFIX_shipment` (
    `id_shipment` int(10) AUTO_INCREMENT NOT NULL,
    `id_order` int(10) NOT NULL,
    `id_carrier` int(10) NOT NULL,
    `id_delivery_address` int(10) DEFAULT NULL,
    `shipping_cost_tax_excl` NUMERIC(20, 6) DEFAULT '0.000000',
    `shipping_cost_tax_incl` NUMERIC(20, 6) DEFAULT '0.000000',
    `packed_at` datetime DEFAULT NULL,
    `shipped_at` datetime DEFAULT NULL,
    `delivered_at` datetime DEFAULT NULL,
    `tracking_number` varchar(255) DEFAULT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`id_shipment`),
) ENGINE = ENGINE_TYPE DEFAULT CHARSET = utf8mb4 COLLATION;

CREATE TABLE IF NOT EXISTS `PREFIX_shipment_product` (
    `shipment_product_id` int(10) NOT NULL,
    `shipment_id` int(10) NOT NULL,
    `order_detail_id` int(10) NOT NULL,
    `quantity` int(10) DEFAULT NULL,
    PRIMARY KEY (shipment_id),
) ENGINE = ENGINE_TYPE DEFAULT CHARSET = utf8mb4 COLLATION;
