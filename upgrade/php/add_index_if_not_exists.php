<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
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
