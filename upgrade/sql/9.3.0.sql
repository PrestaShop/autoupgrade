SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

-- https://github.com/PrestaShop/PrestaShop/pull/43548
-- EU withdrawal function (Directive (EU) 2023/2673)
CREATE TABLE IF NOT EXISTS `PREFIX_withdrawal_request` (
  `id_withdrawal_request` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_shop` int(10) UNSIGNED NOT NULL,
  `id_order` int(10) UNSIGNED NOT NULL,
  `id_customer` int(10) UNSIGNED NOT NULL,
  `order_reference` varchar(64) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(32) NOT NULL,
  `submitted_at` datetime NOT NULL,
  `acknowledged_at` datetime DEFAULT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_withdrawal_request`),
  UNIQUE KEY `id_order` (`id_order`),
  KEY `id_shop_status` (`id_shop`, `status`),
  KEY `id_customer` (`id_customer`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* PHP:add_configuration_if_not_exists('PS_WITHDRAWAL_FUNCTION', '1'); */;
/* PHP:add_configuration_if_not_exists('PS_WITHDRAWAL_PERIOD', '14'); */;

INSERT IGNORE INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES
  ('actionWithdrawalRequestSubmitted', 'Withdrawal request submitted', 'This hook is called after a customer confirmed the withdrawal from an order with the withdrawal function, once the acknowledgement of receipt is sent', '1'),
  ('actionWithdrawalRequestStatusUpdate', 'Withdrawal request status updated', 'This hook is called after the merchant updated the processing status of a withdrawal request', '1'),
  ('actionWithdrawalRequestGridDefinitionModifier', 'Modify withdrawal request grid definition', 'This hook allows to alter withdrawal request grid columns, actions and filters', '1'),
  ('actionWithdrawalRequestGridQueryBuilderModifier', 'Modify withdrawal request grid query builder', 'This hook allows to alter Doctrine query builder for withdrawal request grid', '1');

INSERT IGNORE INTO `PREFIX_meta` (`page`, `configurable`) VALUES ('withdrawal', 1);

INSERT IGNORE INTO `PREFIX_meta_lang` (`id_meta`, `id_shop`, `id_lang`, `title`, `description`, `url_rewrite`)
SELECT m.`id_meta`, s.`id_shop`, l.`id_lang`, 'Withdraw from a contract', '', m.`page`
FROM `PREFIX_meta` m
CROSS JOIN `PREFIX_shop` s
CROSS JOIN `PREFIX_lang` l
WHERE m.`page` = 'withdrawal';

/* PHP:ps_930_withdrawal_requests_tab(); */;
