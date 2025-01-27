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
use PrestaShopBundle\Security\Voter\PageVoter;

/**
 * Common method to handle the tab registration
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 *
 * @internal
 */
function register_tab($className, $name, $id_parent, $returnId = false, $parentTab = null, $module = '')
{
    if (null !== $parentTab && !empty($parentTab) && strtolower(trim($parentTab)) !== 'null') {
        $id_parent = (int) DbWrapper::getValue('SELECT `id_tab` FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` = \'' . pSQL($parentTab) . '\'');
    }

    $array = [];
    foreach (explode('|', $name) as $item) {
        $temp = explode(':', $item);
        $array[$temp[0]] = $temp[1];
    }

    if (!(int) DbWrapper::getValue('SELECT count(id_tab) FROM `' . _DB_PREFIX_ . 'tab` WHERE `class_name` = \'' . pSQL($className) . '\' ')) {
        DbWrapper::execute('INSERT INTO `' . _DB_PREFIX_ . 'tab` (`id_parent`, `class_name`, `module`, `position`) VALUES (' . (int) $id_parent . ', \'' . pSQL($className) . '\', \'' . pSQL($module) . '\',
									(SELECT IFNULL(MAX(t.position),0)+ 1 FROM `' . _DB_PREFIX_ . 'tab` t WHERE t.id_parent = ' . (int) $id_parent . '))');
    }

    $languages = DbWrapper::executeS('SELECT id_lang, iso_code FROM `' . _DB_PREFIX_ . 'lang`');
    foreach ($languages as $lang) {
        DbWrapper::execute('
		INSERT IGNORE INTO `' . _DB_PREFIX_ . 'tab_lang` (`id_lang`, `id_tab`, `name`)
		VALUES (' . (int) $lang['id_lang'] . ', (
				SELECT `id_tab`
				FROM `' . _DB_PREFIX_ . 'tab`
				WHERE `class_name` = \'' . pSQL($className) . '\' LIMIT 0,1
			), \'' . pSQL(isset($array[$lang['iso_code']]) ? $array[$lang['iso_code']] : $array['en']) . '\')
		');
    }
}

/**
 * Common method for getting the new tab ID
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 *
 * @internal
 */
function get_new_tab_id($className, $returnId = false)
{
    if ($returnId && strtolower(trim($returnId)) !== 'false') {
        return (int) DbWrapper::getValue('SELECT `id_tab`
								FROM `' . _DB_PREFIX_ . 'tab`
								WHERE `class_name` = \'' . pSQL($className) . '\'');
    }
}

/**
 * Entrypoint for adding new tabs on +1.7 versions of PrestaShop
 *
 * @param string $className
 * @param string $name Pipe-separated translated values
 * @param int $id_parent
 * @param bool $returnId
 * @param string $parentTab
 * @param string $module
 *
 * @return int|null Tab id if requested
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function add_new_tab_17($className, $name, $id_parent, $returnId = false, $parentTab = null, $module = '')
{
    register_tab($className, $name, $id_parent, $returnId, $parentTab, $module);

    // Preliminary - Get Parent class name for slug generation
    $parentClassName = null;
    if ($id_parent) {
        $parentClassName = DbWrapper::getValue('
            SELECT `class_name`
            FROM `' . _DB_PREFIX_ . 'tab`
            WHERE `id_tab` = "' . (int) $id_parent . '"
        ');
    } elseif (!empty($parentTab)) {
        $parentClassName = $parentTab;
    }

    foreach ([PageVoter::CREATE, PageVoter::READ, PageVoter::UPDATE, PageVoter::DELETE] as $role) {
        // 1- Add role
        $roleToAdd = strtoupper('ROLE_MOD_TAB_' . $className . '_' . $role);
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

        // 2- Copy access from the parent
        if (!empty($parentClassName) && !empty($newID)) {
            $parentRole = strtoupper('ROLE_MOD_TAB_' . pSQL($parentClassName) . '_' . $role);
            DbWrapper::execute(
                'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'access` (`id_profile`, `id_authorization_role`)
                SELECT a.`id_profile`, ' . (int) $newID . ' as `id_authorization_role`
                FROM `' . _DB_PREFIX_ . 'access` a join `' . _DB_PREFIX_ . 'authorization_role` ar on a.`id_authorization_role` = ar.`id_authorization_role`
                WHERE ar.`slug` = "' . pSQL($parentRole) . '"'
            );
        }
    }

    return get_new_tab_id($className, $returnId);
}
