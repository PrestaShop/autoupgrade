<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * This function creates an index if it does not exist. Particularly useful for catch-up scripts where the creation could be run twice.
 *
 * Note: if the index already exists, the script does not check if the requested columns are the same as the existing ones. It just returns.
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function add_index_if_not_exists(string $table, string $index, string $parameters): bool
{
    // Verify if we need to create unique key
    $keys = DbWrapper::executeS(
        'SHOW KEYS FROM `' . _DB_PREFIX_ . pSQL($table) . "` WHERE Key_name='" . pSQL($index) . "'"
    );

    if (!empty($keys)) {
        return true;
    }

    return DbWrapper::execute('ALTER TABLE `' . _DB_PREFIX_ . pSQL($table) . '` ADD INDEX `' . pSQL($index) . '` ' . pSQL($parameters));
}
