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
 * Migrate cart_rule.type to the new cart_rule_type structure
 * This function:
 * 1. Extracts unique types from cart_rule table
 * 2. Populates cart_rule_type table
 * 3. Adds id_cart_rule_type column to cart_rule
 * 4. Migrates data from type to id_cart_rule_type
 * 5. Drops the old type column
 *
 * @return void
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_910_migrate_cart_rule_types()
{
    $now = date('Y-m-d H:i:s');

    // Check if cart_rule table has the old 'type' column
    $columns = DbWrapper::executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'cart_rule` WHERE Field = "type"');

    if (empty($columns)) {
        return;
    }

    // Get all discount types from cart_rule table
    $existingTypes = DbWrapper::executeS(
        'SELECT DISTINCT `type` FROM `' . _DB_PREFIX_ . 'cart_rule` WHERE `type` IS NOT NULL AND `type` != ""'
    );

    $languages = DbWrapper::executeS('SELECT `id_lang` FROM `' . _DB_PREFIX_ . 'lang`');

    foreach ($existingTypes as $typeRow) {
        $type = pSQL($typeRow['type']);

        // Check if this type already exists in cart_rule_type
        $existingType = DbWrapper::executeS(
            'SELECT `id_cart_rule_type` FROM `' . _DB_PREFIX_ . 'cart_rule_type` WHERE `type` = "' . $type . '"'
        );

        if (!empty($existingType)) {
            continue;
        }

        DbWrapper::execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_type` 
            (`type`, `is_core`, `active`, `date_add`, `date_upd`) 
            VALUES ("' . $type . '", 1, 1, "' . $now . '", "' . $now . '")'
        );

        $idCartRuleType = (int) DbWrapper::Insert_ID();

        foreach ($languages as $lang) {
            $idLang = (int) $lang['id_lang'];

            $name = pSQL(ucwords(str_replace('_', ' ', $typeRow['type'])));

            DbWrapper::execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_type_lang` 
                (`id_cart_rule_type`, `id_lang`, `name`, `description`) 
                VALUES (' . $idCartRuleType . ', ' . $idLang . ', "' . $name . '", "")'
            );
        }
    }

    $idTypeColumnExists = DbWrapper::executeS(
        'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'cart_rule` WHERE Field = "id_cart_rule_type"'
    );

    if (empty($idTypeColumnExists)) {
        DbWrapper::execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'cart_rule` 
            ADD `id_cart_rule_type` int(10) unsigned DEFAULT NULL AFTER `date_upd`'
        );
    }

    DbWrapper::execute(
        'UPDATE `' . _DB_PREFIX_ . 'cart_rule` cr 
        INNER JOIN `' . _DB_PREFIX_ . 'cart_rule_type` crt ON cr.`type` COLLATE utf8mb4_unicode_ci = crt.`type` COLLATE utf8mb4_unicode_ci
        SET cr.`id_cart_rule_type` = crt.`id_cart_rule_type` 
        WHERE cr.`type` IS NOT NULL AND cr.`type` != ""'
    );

    $indexes = DbWrapper::executeS(
        'SHOW INDEX FROM `' . _DB_PREFIX_ . 'cart_rule` WHERE Key_name = "type"'
    );

    if (!empty($indexes)) {
        DbWrapper::execute('ALTER TABLE `' . _DB_PREFIX_ . 'cart_rule` DROP INDEX `type`');
    }

    DbWrapper::execute('ALTER TABLE `' . _DB_PREFIX_ . 'cart_rule` DROP COLUMN `type`');

    $newIndexes = DbWrapper::executeS(
        'SHOW INDEX FROM `' . _DB_PREFIX_ . 'cart_rule` WHERE Key_name = "id_cart_rule_type"'
    );

    if (empty($newIndexes)) {
        DbWrapper::execute('ALTER TABLE `' . _DB_PREFIX_ . 'cart_rule` ADD INDEX `id_cart_rule_type` (`id_cart_rule_type`)');
    }
}
