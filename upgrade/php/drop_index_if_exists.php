<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * Drops an index only if the table actually carries it.
 *
 * MySQL has no `DROP INDEX IF EXISTS`, so a migration that drops an index outright fails with
 * error 1091 on any shop where the index is already gone - which stops the update after earlier
 * statements have run. This is the counterpart of add_index_if_not_exists().
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function drop_index_if_exists(string $table, string $index): bool
{
    $keys = DbWrapper::executeS(
        'SHOW KEYS FROM `' . _DB_PREFIX_ . pSQL($table) . "` WHERE Key_name='" . pSQL($index) . "'"
    );

    if (empty($keys)) {
        return true;
    }

    return DbWrapper::execute('ALTER TABLE `' . _DB_PREFIX_ . pSQL($table) . '` DROP INDEX `' . pSQL($index) . '`');
}
