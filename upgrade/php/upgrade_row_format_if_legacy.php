<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * Converts a table still using one of InnoDB's legacy row formats to DYNAMIC.
 *
 * COMPACT and REDUNDANT cap an index at 767 bytes per column, which is less than a utf8mb4
 * VARCHAR(255) needs (1020). Widening an indexed column to 255 characters on such a table is
 * refused - "Specified key was too long" on MySQL, "Index column size too large" on MariaDB -
 * and the update stops in the middle of the schema migration. A row format is only ever set
 * when a table is created or rebuilt, so a shop that has been updated since the days when
 * COMPACT was the default still carries it. DYNAMIC raises that cap to 3072 bytes.
 *
 * A table already using DYNAMIC or COMPRESSED is left alone: the conversion rebuilds the
 * table, which is not free on a large one, and COMPRESSED has the same 3072 byte cap.
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function upgrade_row_format_if_legacy(string $table): bool
{
    $rowFormat = DbWrapper::getValue(
        'SELECT ROW_FORMAT FROM information_schema.TABLES'
        . " WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . pSQL(_DB_PREFIX_ . $table) . "'"
    );

    if (!in_array(strtoupper((string) $rowFormat), ['COMPACT', 'REDUNDANT'], true)) {
        return true;
    }

    return DbWrapper::execute('ALTER TABLE `' . _DB_PREFIX_ . pSQL($table) . '` ROW_FORMAT=DYNAMIC');
}
