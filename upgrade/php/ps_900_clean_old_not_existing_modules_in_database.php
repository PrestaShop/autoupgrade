<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
function ps_900_clean_old_not_existing_modules_in_database()
{

    $modules = Db::getInstance()->ExecuteS("SELECT `name` FROM `" . _DB_PREFIX_ . "module` ORDER BY `name` ASC;");

    if ($modules) {
        foreach ($modules as $m) {
            $module = $m['name'];
            if (!Module::getInstanceByName($module) && !file_exists(_PS_MODULE_DIR_ . $module)) {
                Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "module` WHERE `name` = '$module';");
            }
        }

        Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "hook_module` WHERE `id_module` NOT IN (SELECT `id_module` FROM `" . _DB_PREFIX_ . "module`);");

        Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "hook_module_exceptions` WHERE `id_module` NOT IN (SELECT `id_module` FROM `" . _DB_PREFIX_ . "module`);");

        Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "module_carrier` WHERE `id_module` NOT IN (SELECT `id_module` FROM `" . _DB_PREFIX_ . "module`);");

        Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "module_country` WHERE `id_module` NOT IN (SELECT `id_module` FROM `" . _DB_PREFIX_ . "module`);");

        Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "module_currency` WHERE `id_module` NOT IN (SELECT `id_module` FROM `" . _DB_PREFIX_ . "module`);");

        Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "module_group` WHERE `id_module` NOT IN (SELECT `id_module` FROM `" . _DB_PREFIX_ . "module`);");

        Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "module_history` WHERE `id_module` NOT IN (SELECT `id_module` FROM `" . _DB_PREFIX_ . "module`);");

        Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "module_shop` WHERE `id_module` NOT IN (SELECT `id_module` FROM `" . _DB_PREFIX_ . "module`);");

        Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "module_preference` WHERE `module` NOT IN (SELECT `name` FROM `" . _DB_PREFIX_ . "module`);");

        Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "tab_module_preference` WHERE `module` NOT IN (SELECT `name` FROM `" . _DB_PREFIX_ . "module`);");
    }
}
