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
 * @return true
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_811_update_redirect_type()
{
    // Get information about redirect_type column
    $columnInformation = DbWrapper::executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'product` LIKE "redirect_type"');
    if (empty($columnInformation)) {
        return true;
    }

    // Get first record found
    $columnInformation = reset($columnInformation);

    // Check if it was already upgraded and contains new information
    if (strpos($columnInformation['Type'], '200-displayed') !== false) {
        return true;
    }

    // If not, we execute our upgrade queries
    DbWrapper::execute('ALTER TABLE `' . _DB_PREFIX_ . "product` MODIFY COLUMN `redirect_type` ENUM(
        '','404','410','301-product','302-product','301-category','302-category','200-displayed','404-displayed','410-displayed','default'
        ) NOT NULL DEFAULT 'default';");
    DbWrapper::execute('ALTER TABLE `' . _DB_PREFIX_ . "product_shop` MODIFY COLUMN `redirect_type` ENUM(
        '','404','410','301-product','302-product','301-category','302-category','200-displayed','404-displayed','410-displayed','default'
        ) NOT NULL DEFAULT 'default';");
    DbWrapper::execute('UPDATE `' . _DB_PREFIX_ . "product` SET `redirect_type` = 'default' WHERE `redirect_type` = '404' OR `redirect_type` = '' OR `redirect_type` IS NULL;");
    DbWrapper::execute('UPDATE `' . _DB_PREFIX_ . "product_shop` SET `redirect_type` = 'default' WHERE `redirect_type` = '404' OR `redirect_type` = '' OR `redirect_type` IS NULL;");

    return true;
}
