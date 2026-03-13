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
function add_configuration_if_not_exists($name, $value)
{
    // Check if this record already exists
    $entry_exists = DbWrapper::executeS('SELECT * FROM `' . _DB_PREFIX_ . "configuration` WHERE name = '" . $name . "'");

    // If no rows were found, insert a new entry
    if (empty($entry_exists)) {
        DbWrapper::execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'configuration` (`name`, `value`, `date_add`, `date_upd`)
            VALUES (\'' . pSQL($name) . '\', \'' . pSQL($value) . '\', NOW(), NOW())'
        );
    }
}
