<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * Allows you to catch up on a forgotten uniqueness constraint on the roles
 *
 * @return void
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function add_missing_unique_key_from_authorization_role()
{
    // Verify if we need to create unique key
    $keys = DbWrapper::executeS(
        'SHOW KEYS FROM ' . _DB_PREFIX_ . "authorization_role WHERE Key_name='slug'"
    );

    if (!empty($keys)) {
        return;
    }

    // We recover the duplicates that we want to keep
    $duplicates = DbWrapper::executeS(
        'SELECT MIN(id_authorization_role) AS keep_ID, slug FROM ' . _DB_PREFIX_ . 'authorization_role GROUP BY slug HAVING COUNT(*) > 1'
    );

    if (empty($duplicates)) {
        return;
    }

    foreach ($duplicates as $duplicate) {
        // We recover the duplicates that we want to remove
        $elementsToRemoves = DbWrapper::executeS(
            'SELECT id_authorization_role FROM ' . _DB_PREFIX_ . "authorization_role WHERE slug = '" . $duplicate['slug'] . "' AND id_authorization_role != " . $duplicate['keep_ID']
        );

        foreach ($elementsToRemoves as $elementToRemove) {
            // We update the access table which may have used a duplicate role
            DbWrapper::execute(
                'UPDATE ' . _DB_PREFIX_ . "access SET id_authorization_role = '" . $duplicate['keep_ID'] . "' WHERE id_authorization_role = " . $elementToRemove['id_authorization_role']
            );
            // We remove the role
            DbWrapper::delete('authorization_role', '`id_authorization_role` = ' . (int) $elementToRemove['id_authorization_role']);
        }
    }

    DbWrapper::execute(
        'ALTER TABLE ' . _DB_PREFIX_ . 'authorization_role ADD UNIQUE KEY `slug` (`slug`)'
    );
}
