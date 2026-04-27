<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
