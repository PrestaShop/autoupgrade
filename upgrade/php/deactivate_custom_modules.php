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
 * @return bool
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function deactivate_custom_modules(): bool
{
    $modules = scandir(_PS_MODULE_DIR_, SCANDIR_SORT_NONE);
    foreach ($modules as $name) {
        if (!in_array($name, ['.', '..', 'index.php', '.htaccess']) && @is_dir(_PS_MODULE_DIR_ . $name . DIRECTORY_SEPARATOR) && @file_exists(_PS_MODULE_DIR_ . $name . DIRECTORY_SEPARATOR . $name . '.php')) {
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
                exit(Tools::displayError() . ' (Module ' . $name . ')');
            }
        }
    }

    $module_list_xml = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'modules_list.xml';

    if (!file_exists($module_list_xml)) {
        $module_list_xml = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'modules_list.xml';
        if (!file_exists($module_list_xml)) {
            return false;
        }
    }

    $nativeModules = @simplexml_load_file($module_list_xml);
    if ($nativeModules) {
        $nativeModules = $nativeModules->modules;
    }
    $arrNativeModules = [];
    if (!empty($nativeModules)) {
        foreach ($nativeModules as $nativeModulesType) {
            if (in_array($nativeModulesType['type'], ['native', 'partner'])) {
                $arrNativeModules[] = '""';
                foreach ($nativeModulesType->module as $module) {
                    $arrNativeModules[] = '"' . pSQL($module['name']) . '"';
                }
            }
        }
    }
    $arrNonNative = [];
    if ($arrNativeModules) {
        $arrNonNative = DbWrapper::executeS('
    		SELECT *
    		FROM `' . _DB_PREFIX_ . 'module` m
    		WHERE name NOT IN (' . implode(',', $arrNativeModules) . ') ');
    }

    $uninstallMe = ['undefined-modules'];
    if (is_array($arrNonNative)) {
        foreach ($arrNonNative as $k => $aModule) {
            $uninstallMe[(int) $aModule['id_module']] = $aModule['name'];
        }
    }

    foreach ($uninstallMe as $k => $v) {
        $uninstallMe[$k] = '"' . pSQL($v) . '"';
    }

    $return = DbWrapper::execute('
	UPDATE `' . _DB_PREFIX_ . 'module` SET `active` = 0 WHERE `name` IN (' . implode(',', $uninstallMe) . ')');

    if (count(DbWrapper::executeS('SHOW TABLES LIKE \'' . _DB_PREFIX_ . 'module_shop\'')) > 0) {
        foreach ($uninstallMe as $k => $uninstall) {
            $return &= DbWrapper::execute('DELETE FROM `' . _DB_PREFIX_ . 'module_shop` WHERE `id_module` = ' . (int) $k);
        }
    }

    return $return;
}

/**
 * @param mixed $moduleRepository
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function deactivate_custom_modules80($moduleRepository): bool
{
    $nonNativeModulesList = $moduleRepository->getNonNativeModules();

    $return = DbWrapper::execute(
        'UPDATE `' . _DB_PREFIX_ . 'module` SET `active` = 0 WHERE `name` IN (' . implode(',', array_map('add_quotes', $nonNativeModulesList)) . ')'
    );

    $nonNativeModules = DbWrapper::executeS('
        SELECT *
        FROM `' . _DB_PREFIX_ . 'module` m
        WHERE name IN (' . implode(',', array_map('add_quotes', $nonNativeModulesList)) . ') ');

    if (!is_array($nonNativeModules) || empty($nonNativeModules)) {
        return $return;
    }

    $toBeUninstalled = [];
    foreach ($nonNativeModules as $aModule) {
        $toBeUninstalled[] = (int) $aModule['id_module'];
    }

    $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'module_shop` WHERE `id_module` IN (' . implode(',', array_map('add_quotes', $toBeUninstalled)) . ') ';

    return $return && DbWrapper::execute($sql);
}

function add_quotes(string $str): string
{
    return sprintf("'%s'", $str);
}
