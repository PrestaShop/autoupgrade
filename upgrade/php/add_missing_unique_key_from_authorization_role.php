<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

// Allows you to catch up on a forgotten uniqueness constraint on the roles
function add_missing_unique_key_from_authorization_role()
{
    // Verify if we need to create unique key
    $keys = Db::getInstance()->executeS(
        'SHOW KEYS FROM ' . _DB_PREFIX_ . "authorization_role WHERE Key_name='slug'"
    );

    if (!empty($keys)) {
        return;
    }

    // We recover the duplicates that we want to keep
    $duplicates = Db::getInstance()->executeS(
        'SELECT MIN(id_authorization_role) AS keep_ID, slug FROM ' . _DB_PREFIX_ . 'authorization_role GROUP BY slug HAVING COUNT(*) > 1'
    );

    if (empty($duplicates)) {
        return;
    }

    foreach ($duplicates as $duplicate) {
        // We recover the duplicates that we want to remove
        $elementsToRemoves = Db::getInstance()->executeS(
            'SELECT id_authorization_role FROM ' . _DB_PREFIX_ . "authorization_role WHERE slug = '" . $duplicate['slug'] . "' AND id_authorization_role != " . $duplicate['keep_ID']
        );

        foreach ($elementsToRemoves as $elementToRemove) {
            // We update the access table which may have used a duplicate role
            Db::getInstance()->execute(
                'UPDATE ' . _DB_PREFIX_ . "access SET id_authorization_role = '" . $duplicate['keep_ID'] . "' WHERE id_authorization_role = " . $elementToRemove['id_authorization_role']
            );
            // We remove the role
            Db::getInstance()->delete('authorization_role', '`id_authorization_role` = ' . (int) $elementToRemove['id_authorization_role']);
        }
    }

    Db::getInstance()->execute(
        'ALTER TABLE ' . _DB_PREFIX_ . 'authorization_role ADD UNIQUE KEY `slug` (`slug`)'
    );
}
