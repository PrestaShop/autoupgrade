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
 * Since note is now TYPE_STRING instead of TYPE_HTML it needs to be decoded
 *
 * @return bool
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_1771_update_customer_note()
{
    $notes = DbWrapper::executeS(
        'SELECT id_customer, note FROM ' . _DB_PREFIX_ . 'customer
        WHERE note IS NOT NULL AND note != ""'
    );

    $result = true;
    foreach ($notes as $note) {
        $result &= DbWrapper::execute(
            'UPDATE ' . _DB_PREFIX_ . 'customer
            SET note = "' . pSQL(html_entity_decode($note['note'])) . '"
            WHERE id_customer = ' . $note['id_customer']
        );
    }

    return (bool) $result;
}
