<?php
/**
 * 2007-2019 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
function upgrade_module_5_0_0($module)
{
    //Install module controller (hidden)
    $controller_id_tab = (int) Tab::getIdFromClassName('AutoupgradeModule');
    if (!$controller_id_tab) {
        $controllerTab = new Tab();
        $controllerTab->active = 0;
        $controllerTab->class_name = 'AutoupgradeModule';
        $controllerTab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $controllerTab->name[$lang['id_lang']] = 'AutoupgradeModule';
        }
        $controllerTab->id_parent = (int) Tab::getIdFromClassName('AdminTools');
        $controllerTab->module = $module->name;

        $controllerTab->add();
    }
}
