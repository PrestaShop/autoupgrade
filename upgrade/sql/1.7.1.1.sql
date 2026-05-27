SET NAMES 'utf8';

ALTER TABLE `PREFIX_address` CHANGE `company` `company` VARCHAR(255) DEFAULT NULL;

/* PHP:drop_index_if_exists('attribute', 'FK_6C3355F967A664FB', 'FOREIGN KEY'); */;
/* PHP:drop_index_if_exists('attribute_group_lang', 'FK_4653726C67A664FB', 'FOREIGN KEY'); */;
/* PHP:drop_index_if_exists('attribute_group_shop', 'FK_DB30BAAC274A50A0', 'FOREIGN KEY'); */;
/* PHP:drop_index_if_exists('attribute_group_shop', 'FK_DB30BAAC67A664FB', 'FOREIGN KEY'); */;
/* PHP:drop_index_if_exists('attribute_lang', 'FK_3ABE46A77A4F53DC', 'FOREIGN KEY'); */;
/* PHP:drop_index_if_exists('attribute_shop', 'FK_A7DD8E67274A50A0', 'FOREIGN KEY'); */;
/* PHP:drop_index_if_exists('attribute_shop', 'FK_A7DD8E677A4F53DC', 'FOREIGN KEY'); */;
/* PHP:drop_index_if_exists('lang_shop', 'FK_2F43BFC7274A50A0', 'FOREIGN KEY'); */;
/* PHP:drop_index_if_exists('lang_shop', 'FK_2F43BFC7BA299860', 'FOREIGN KEY'); */;
/* PHP:drop_index_if_exists('shop', 'FK_CBDFBB9EF5C9E40', 'FOREIGN KEY'); */;
/* PHP:drop_index_if_exists('tab_lang', 'FK_CFD9262DED47AB56', 'FOREIGN KEY'); */;
/* PHP:drop_index_if_exists('translation', 'FK_ADEBEB36BA299860', 'FOREIGN KEY'); */;

ALTER TABLE `PREFIX_tab` CHANGE icon icon VARCHAR(32) DEFAULT NULL;

