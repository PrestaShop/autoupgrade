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
function add_column($table, $column, $parameters)
{
    $column_exists = DbWrapper::executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . $table . "` WHERE Field = '" . $column . "'");

    if (!empty($column_exists)) {
        // If it already exists, we will modify the structure
        DbWrapper::execute('ALTER TABLE `' . _DB_PREFIX_ . $table . '` CHANGE `' . $column . '` `' . $column . '` ' . $parameters);
    } else {
        // Otherwise, we add it
        DbWrapper::execute('ALTER TABLE `' . _DB_PREFIX_ . $table . '` ADD `' . $column . '` ' . $parameters);
    }
}
