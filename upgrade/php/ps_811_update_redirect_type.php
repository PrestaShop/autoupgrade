<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
