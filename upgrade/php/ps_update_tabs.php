<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * @return void
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_update_tabs()
{
    if (file_exists(__DIR__ . '/../../data/xml/tab.xml')) {
        $tab_xml = simplexml_load_file(__DIR__ . '/../../data/xml/tab.xml');
        if (!empty($tab_xml)) {
            $tab_class_name = [];
            $tab_ids = [];

            foreach ($tab_xml->entities->tab as $tab) {
                $tab = (array) $tab;
                $tab_class_name[$tab['class_name']] = $tab['@attributes']['id'];
            }

            $tabs = DbWrapper::executeS('SELECT * FROM `' . _DB_PREFIX_ . 'tab`', true, false);
            if (!empty($tabs)) {
                foreach ($tabs as $tab) {
                    if (isset($tab_class_name[$tab['class_name']])) {
                        $tab_ids[$tab['class_name']] = $tab['id_tab'];
                    }
                }
            }
        } else {
            return;
        }

        if (!empty($tab_class_name)) {
            $langs = DbWrapper::executeS('SELECT * FROM `' . _DB_PREFIX_ . 'lang` WHERE `iso_code` != "en" ', true, false);

            if (!empty($langs)) {
                foreach ($langs as $lang) {
                    if (file_exists(__DIR__ . '/../../langs/' . $lang['iso_code'] . '/data/tab.xml')) {
                        // store XML data
                        $tab_xml_data = [];
                        $tab_xml_lang = simplexml_load_file(__DIR__ . '/../../langs/' . $lang['iso_code'] . '/data/tab.xml');
                        if (!empty($tab_xml_lang)) {
                            foreach ($tab_xml_lang->tab as $tab) {
                                $tab = (array) $tab;
                                $tab_xml_data[$tab['@attributes']['id']] = $tab['@attributes']['name'];
                            }
                        }

                        // store DB data
                        $tab_db_data = [];
                        $results = DbWrapper::executeS('
                          SELECT t.`id_tab`, tl.`id_lang`, t.`class_name`, tl.`name` FROM `' . _DB_PREFIX_ . 'tab` t
                            INNER JOIN `' . _DB_PREFIX_ . 'tab_lang` tl ON tl.`id_tab` = t.`id_tab`
                            WHERE tl.`id_lang` = ' . (int) $lang['id_lang'], true, false);

                        if (!empty($results)) {
                            foreach ($results as $res) {
                                $tab_db_data[$res['class_name']] = $res['name'];
                            }
                        }

                        if (!empty($tab_xml_data)) {
                            foreach ($tab_xml_data as $k => $tab) {
                                if (in_array($k, $tab_class_name)) {
                                    $tmp_class_name = array_keys($tab_class_name, $k)[0];

                                    if (array_key_exists($tmp_class_name, $tab_ids)) {
                                        $tmp_class_id = $tab_ids[$tmp_class_name];

                                        // if data XML is not in DB => insert
                                        if (!array_key_exists($tmp_class_name, $tab_db_data)) {
                                            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'tab_lang`
                                            (`id_tab`, `id_lang`, `name`)
                                            VALUES (' . (int) $tmp_class_id . ',' . (int) $lang['id_lang'] . ',"' . pSQL($tab) . '")';

                                            DbWrapper::execute($sql);
                                        } else {
                                            // if DB is != XML
                                            if ($tab_db_data[$tmp_class_name] != $tab) {
                                                $sql = 'UPDATE `' . _DB_PREFIX_ . 'tab_lang`
                                                    SET  `name` = "' . pSQL($tab) . '"
                                                    WHERE   `id_tab` = ' . (int) $tmp_class_id . ' AND
                                                            `id_lang` = ' . (int) $lang['id_lang'] . ' AND
                                                            `name`  = "' . pSQL($tab_db_data[$tmp_class_name]) . '" ';

                                                DbWrapper::execute($sql);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
