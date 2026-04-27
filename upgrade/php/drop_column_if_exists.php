<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * @return void
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function drop_column_if_exists($table, $column)
{
    $column_exists = DbWrapper::executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . $table . "` WHERE Field = '" . $column . "'");

    if (!empty($column_exists)) {
        DbWrapper::execute('ALTER TABLE `' . _DB_PREFIX_ . $table . '` DROP COLUMN `' . $column . '`');
    }
}
