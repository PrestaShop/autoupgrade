<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

function ps_915_drop_address_country_index(): bool
{
    // Shops that ran into the slow orders grid were told to drop this index by hand, so it can
    // already be gone. DROP INDEX on a missing index is an error and MySQL has no IF EXISTS for
    // indexes, so check before dropping rather than fail the upgrade on exactly those shops.
    $existingIndex = DbWrapper::executeS(
        'SHOW INDEX FROM `' . _DB_PREFIX_ . 'address` WHERE `Key_name` = \'id_country\''
    );

    if (empty($existingIndex)) {
        return true;
    }

    return DbWrapper::execute('ALTER TABLE `' . _DB_PREFIX_ . 'address` DROP INDEX `id_country`');
}
