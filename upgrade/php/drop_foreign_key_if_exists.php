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

function drop_foreign_key_if_exists(string $table, string $foreignKey): bool
{
  $exists = DbWrapper::executeS('SELECT CONSTRAINT_NAME
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = \'' . _DB_PREFIX_ . pSQL($table) . '\'
    AND CONSTRAINT_NAME = \'' . pSQL($foreignKey) . '\'
    AND CONSTRAINT_TYPE = \'FOREIGN KEY\''
  );

  if (empty($exists)) {
    return true;
  }

  return DbWrapper::execute(
    'ALTER TABLE `' . _DB_PREFIX_ . pSQL($table) . '` DROP FOREIGN KEY `' . pSQL($foreignKey) . '`'
  );
}
