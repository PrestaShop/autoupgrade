<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;
use PrestaShopBundle\Security\Voter\PageVoter;

/**
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function copy_tab_rights($fromTabName, $toTabName)
{
    if (empty($fromTabName) || empty($toTabName)) {
        return;
    }
    foreach ([PageVoter::CREATE, PageVoter::READ, PageVoter::UPDATE, PageVoter::DELETE] as $role) {
        // 1- Add role
        $roleToAdd = strtoupper('ROLE_MOD_TAB_' . $toTabName . '_' . $role);
        DbWrapper::execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'authorization_role` (`slug`)
            VALUES ("' . pSQL($roleToAdd) . '")');
        $newID = DbWrapper::Insert_ID();
        if (!$newID) {
            $newID = DbWrapper::getValue('
                SELECT `id_authorization_role`
                FROM `' . _DB_PREFIX_ . 'authorization_role`
                WHERE `slug` = "' . pSQL($roleToAdd) . '"
            ');
        }

        // 2- Copy access
        if (!empty($newID)) {
            $parentRole = strtoupper('ROLE_MOD_TAB_' . pSQL($fromTabName) . '_' . $role);
            DbWrapper::execute(
                'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'access` (`id_profile`, `id_authorization_role`)
                SELECT a.`id_profile`, ' . (int) $newID . ' as `id_authorization_role`
                FROM `' . _DB_PREFIX_ . 'access` a join `' . _DB_PREFIX_ . 'authorization_role` ar on a.`id_authorization_role` = ar.`id_authorization_role`
                WHERE ar.`slug` = "' . pSQL($parentRole) . '"'
            );
        }
    }
}
