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
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_1770_add_states()
{
    $row = DbWrapper::getRow(
        'SELECT id_country, id_zone FROM `' . _DB_PREFIX_ . 'country` WHERE iso_code = \'IN\''
    );

    // No India, nothing to do
    if (empty($row)) {
        return true;
    }

    $states = [
        'Andhra Pradesh' => 'AP',
        'Arunachal Pradesh' => 'AR',
        'Assam' => 'AS',
        'Bihar' => 'BR',
        'Chhattisgarh' => 'CT',
        'Goa' => 'GA',
        'Gujarat' => 'GJ',
        'Haryana' => 'HR',
        'Himachal Pradesh' => 'HP',
        'Jharkhand' => 'JH',
        'Karnataka' => 'KA',
        'Kerala' => 'KL',
        'Madhya Pradesh' => 'MP',
        'Maharashtra' => 'MH',
        'Manipur' => 'MN',
        'Meghalaya' => 'ML',
        'Mizoram' => 'MZ',
        'Nagaland' => 'NL',
        'Odisha' => 'OR',
        'Punjab' => 'PB',
        'Rajasthan' => 'RJ',
        'Sikkim' => 'SK',
        'Tamil Nadu' => 'TN',
        'Telangana' => 'TG',
        'Tripura' => 'TR',
        'Uttarakhand' => 'UT',
        'Uttar Pradesh' => 'UP',
        'West Bengal' => 'WB',
    ];

    foreach ($states as $key => $value) {
        $duplicateRow = DbWrapper::getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'state` WHERE name =  \'' . $key . '\' AND id_country = ' . (int) $row['id_country'] . ''
        );

        if (!empty($duplicateRow)) {
            continue;
        }

        $query = 'INSERT INTO `' . _DB_PREFIX_ . 'state` (`id_country`, `id_zone`, `name`, `iso_code`, `tax_behavior`, `active`) VALUES (' . (int) $row['id_country'] . ', ' . (int) $row['id_zone'] . ', \'' . $key . '\', \'' . $value . '\', 0, 1);';
        $result = DbWrapper::execute($query);

        if (!$result) {
            return false;
        }
    }

    return true;
}
