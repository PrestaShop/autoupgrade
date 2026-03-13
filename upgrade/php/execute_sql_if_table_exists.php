<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * @return bool
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function execute_sql_if_table_exists($table, $sqlQuery)
{
    if (empty(DbWrapper::executeS('SHOW TABLES LIKE "' . _DB_PREFIX_ . $table . '"'))) {
        return true;
    }

    $sqlQuery = str_replace('PREFIX', _DB_PREFIX_, $sqlQuery);

    return DbWrapper::execute($sqlQuery);
}
