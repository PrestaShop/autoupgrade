<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * Remove duplicate accessory pairs while keeping one complete row per pair.
 *
 * @return void
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_900_remove_duplicate_accessories()
{
    // Find accessory pairs with more than one row.
    $duplicates = DbWrapper::executeS('
        SELECT `id_product_1`, `id_product_2`, COUNT(*) AS `row_count`
        FROM `' . _DB_PREFIX_ . 'accessory`
        GROUP BY `id_product_1`, `id_product_2`
        HAVING COUNT(*) > 1
    ');

    // Delete only surplus rows so one row and its custom column values remain for each pair.
    foreach ($duplicates as $duplicate) {
        DbWrapper::delete(
            'accessory',
            '`id_product_1` = ' . (int) $duplicate['id_product_1'] . ' AND `id_product_2` = ' . (int) $duplicate['id_product_2'],
            (int) $duplicate['row_count'] - 1
        );
    }
}
