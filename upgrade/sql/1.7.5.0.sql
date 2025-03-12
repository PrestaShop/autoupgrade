SET SESSION sql_mode='';
SET NAMES 'utf8';

/* PHP:add_supplier_manufacturer_routes(); */;

/* PHP:ps_1750_update_module_tabs(); */;

/* PHP:add_column('cms_lang', 'head_seo_title', 'varchar(255) DEFAULT NULL AFTER `meta_title`'); */;
ALTER TABLE `PREFIX_cms_lang`
  CHANGE `meta_title` `meta_title` VARCHAR(255) NOT NULL,
  CHANGE `meta_description` `meta_description` VARCHAR(512) DEFAULT NULL;

/* PHP:add_column('stock_available', 'location', 'VARCHAR(255) NOT NULL DEFAULT \'\' AFTER `out_of_stock`'); */;

ALTER TABLE `PREFIX_store`
  CHANGE `email` `email` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `PREFIX_contact`
  CHANGE `email` `email` VARCHAR(255) NOT NULL;

ALTER TABLE `PREFIX_contact_lang`
  CHANGE `name` `name` varchar(255) NOT NULL;

ALTER TABLE `PREFIX_category_lang`
  CHANGE `meta_title` `meta_title` VARCHAR(255) DEFAULT NULL,
  CHANGE `meta_description` `meta_description` VARCHAR(512) DEFAULT NULL;

ALTER TABLE `PREFIX_cms_category_lang`
  CHANGE `meta_title` `meta_title` VARCHAR(255) DEFAULT NULL,
  CHANGE `meta_description` `meta_description` VARCHAR(512) DEFAULT NULL;

ALTER TABLE `PREFIX_customer`
  CHANGE `company` `company` VARCHAR(255),
  CHANGE `email` `email` VARCHAR(255) NOT NULL,
  CHANGE `passwd` `passwd` VARCHAR(255) NOT NULL;

ALTER TABLE `PREFIX_manufacturer_lang`
  CHANGE `meta_title` `meta_title` VARCHAR(255) DEFAULT NULL,
  CHANGE `meta_description` `meta_description` VARCHAR(512) DEFAULT NULL;

ALTER TABLE `PREFIX_employee`
  CHANGE `firstname` `firstname` VARCHAR(255) NOT NULL,
  CHANGE `email` `email` VARCHAR(255) NOT NULL,
  CHANGE `passwd` `passwd` VARCHAR(255) NOT NULL,
  CHANGE `lastname` `lastname` VARCHAR(255) NOT NULL;

ALTER TABLE `PREFIX_referrer`
  CHANGE `passwd` `passwd` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `PREFIX_supply_order_history`
  CHANGE `employee_lastname` `employee_lastname` VARCHAR(255) DEFAULT '',
  CHANGE `employee_firstname` `employee_firstname` VARCHAR(255) DEFAULT '';

ALTER TABLE `PREFIX_supply_order_receipt_history`
  CHANGE `employee_firstname` `employee_firstname` VARCHAR(255) DEFAULT '';

ALTER TABLE `PREFIX_supplier_lang`
  CHANGE `meta_description` `meta_description` VARCHAR(512) DEFAULT NULL,
  CHANGE `meta_title` `meta_title` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `PREFIX_order_detail`
  CHANGE `product_reference` `product_reference` varchar(64) DEFAULT NULL;

ALTER TABLE `PREFIX_product`
  CHANGE `supplier_reference` `supplier_reference` varchar(64) DEFAULT NULL;

ALTER TABLE `PREFIX_product_attribute`
  CHANGE `reference` `reference` varchar(64) DEFAULT NULL,
  CHANGE `supplier_reference` `supplier_reference` varchar(64) DEFAULT NULL;

ALTER TABLE `PREFIX_warehouse`
  CHANGE `reference` `reference` varchar(64) DEFAULT NULL;

ALTER TABLE `PREFIX_stock`
  CHANGE `reference` `reference` varchar(64) NOT NULL;

ALTER TABLE `PREFIX_supply_order_detail`
  CHANGE `reference` `reference` varchar(64) NOT NULL,
  CHANGE `supplier_reference` `supplier_reference` varchar(64) NOT NULL;

ALTER TABLE `PREFIX_product_supplier`
  CHANGE `product_supplier_reference` `product_supplier_reference` varchar(64) DEFAULT NULL;

ALTER TABLE `PREFIX_product_lang`
  CHANGE `meta_description` `meta_description` varchar(512) DEFAULT NULL,
  CHANGE `meta_keywords` `meta_keywords` varchar(255) DEFAULT NULL;

ALTER TABLE `PREFIX_customer_thread`
  CHANGE `email` `email` varchar(255) NOT NULL;

ALTER TABLE `PREFIX_attribute_group_lang`
    ADD KEY `IDX_4653726CBA299860` (`id_lang`);

ALTER TABLE `PREFIX_attribute_lang`
    ADD KEY `IDX_3ABE46A7BA299860` (`id_lang`);

ALTER TABLE `PREFIX_tab_lang`
    ADD KEY `IDX_CFD9262DBA299860` (`id_lang`);

INSERT INTO `PREFIX_hook` (`name`, `title`, `description`, `position`) VALUES
  ('actionBackupGridDefinitionModifier', 'Modifying DB Backup grid definition', 'This hook allows to alter DB Backup grid columns, actions and filters', 1),
  ('actionBackupGridFilterFormModifier', 'Modify filters form for DB Backup grid', 'This hook allows to alter filters form used in DB Backup', 1),
  ('actionBackupGridPresenterModifier', 'Modify DB Backup grid view data', 'This hook allows to alter presented DB Backup grid data', 1),
  ('actionEmailLogsGridDefinitionModifier', 'Modifying E-mail grid definition', 'This hook allows to alter E-mail grid columns, actions and filters', 1),
  ('actionEmailLogsGridFilterFormModifier', 'Modify filters form for E-mail grid', 'This hook allows to alter filters form used in E-mail', 1),
  ('actionEmailLogsGridPresenterModifier', 'Modify E-mail grid view data', 'This hook allows to alter presented E-mail grid data', 1),
  ('actionEmailLogsGridQueryBuilderModifier', 'Modify E-mail grid query builder', 'This hook allows to alter Doctrine query builder for E-mail grid', 1),
  ('actionLogsGridDefinitionModifier', 'Modifying Logs grid definition', 'This hook allows to alter Logs grid columns, actions and filters', 1),
  ('actionLogsGridFilterFormModifier', 'Modify filters form for Logs grid', 'This hook allows to alter filters form used in Logs', 1),
  ('actionLogsGridPresenterModifier', 'Modify Logs grid view data', 'This hook allows to alter presented Logs grid data', 1),
  ('actionLogsGridQueryBuilderModifier', 'Modify Logs grid query builder', 'This hook allows to alter Doctrine query builder for Logs grid', 1),
  ('actionMetaGridDefinitionModifier', 'Modifying SEO and URLs grid definition', 'This hook allows to alter SEO and URLs grid columns, actions and filters', 1),
  ('actionMetaGridFilterFormModifier', 'Modify filters form for SEO and URLs grid', 'This hook allows to alter filters form used in SEO and URLs', 1),
  ('actionMetaGridPresenterModifier', 'Modify SEO and URLs grid view data', 'This hook allows to alter presented SEO and URLs grid data', 1),
  ('actionMetaGridQueryBuilderModifier', 'Modify SEO and URLs grid query builder', 'This hook allows to alter Doctrine query builder for SEO and URLs grid', 1),
  ('actionSqlRequestGridDefinitionModifier', 'Modifying SQL Manager grid definition', 'This hook allows to alter SQL Manager grid columns, actions and filters', 1),
  ('actionSqlRequestGridFilterFormModifier', 'Modify filters form for SQL Manager grid', 'This hook allows to alter filters form used in SQL Manager', 1),
  ('actionSqlRequestGridPresenterModifier', 'Modify SQL Manager grid view data', 'This hook allows to alter presented SQL Manager grid data', 1),
  ('actionSqlRequestGridQueryBuilderModifier', 'Modify SQL Manager grid query builder', 'This hook allows to alter Doctrine query builder for SQL Manager grid', 1),
  ('actionWebserviceKeyGridDefinitionModifier', 'Modifying Webservice grid definition', 'This hook allows to alter Webservice grid columns, actions and filters', 1),
  ('actionWebserviceKeyGridFilterFormModifier', 'Modify filters form for Webservice grid', 'This hook allows to alter filters form used in Webservice', 1),
  ('actionWebserviceKeyGridPresenterModifier', 'Modify Webservice grid view data', 'This hook allows to alter presented Webservice grid data', 1),
  ('actionWebserviceKeyGridQueryBuilderModifier', 'Modify Webservice grid query builder', 'This hook allows to alter Doctrine query builder for Webservice grid', 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);
