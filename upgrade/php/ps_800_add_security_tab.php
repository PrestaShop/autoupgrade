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
function ps_800_add_security_tab()
{
    include_once __DIR__ . '/add_new_tab.php';
    $tabs = [
        ['AdminParentSecurity', 'en:Security', 0, false, 'AdminAdvancedParameters'],
        ['AdminSecurity', 'en:Security', 0, false, 'AdminParentSecurity'],
        ['AdminSecuritySessionEmployee', 'en:Employee Sessions', 0, false, 'AdminParentSecurity'],
        ['AdminSecuritySessionCustomer', 'en:Customer Sessions', 0, false, 'AdminParentSecurity'],
    ];
    $tabsData = [
        'AdminParentSecurity' => [
            'active' => 1,
            'enabled' => 1,
            'wording' => '\'Security\'',
            'wording_domain' => '\'Admin.Navigation.Menu\'',
        ],
        'AdminSecurity' => [
            'active' => 1,
            'enabled' => 1,
            'wording' => '\'Security\'',
            'wording_domain' => '\'Admin.Navigation.Menu\'',
            'route_name' => '\'admin_security\'',
        ],
        'AdminSecuritySessionEmployee' => [
            'active' => 1,
            'enabled' => 1,
            'wording' => '\'Employee Sessions\'',
            'wording_domain' => '\'Admin.Navigation.Menu\'',
            'route_name' => '\'admin_security_sessions_employee_list\'',
        ],
        'AdminSecuritySessionCustomer' => [
            'active' => 1,
            'enabled' => 1,
            'wording' => '\'Customer Sessions\'',
            'wording_domain' => '\'Admin.Navigation.Menu\'',
            'route_name' => '\'admin_security_sessions_customer_list\'',
        ],
    ];

    foreach ($tabs as $tab) {
        add_new_tab_17(...$tab);
        $data = [];
        foreach ($tabsData[$tab[0]] as $key => $value) {
            $data[] = '`' . $key . '` = ' . $value;
        }
        Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'tab` SET ' . implode(', ', $data) . ' WHERE `class_name` = \'' . $tab[0] . '\''
        );
    }
}
