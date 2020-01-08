<?php

/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade;

use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;

/**
 * Clean the database from unwanted entries.
 */
class CleanDatabase extends AbstractTask
{
    public function run()
    {
        // Clean tabs order
        foreach ($this->container->getDb()->ExecuteS('SELECT DISTINCT id_parent FROM ' . _DB_PREFIX_ . 'tab') as $parent) {
            $i = 1;
            foreach ($this->container->getDb()->ExecuteS('SELECT id_tab FROM ' . _DB_PREFIX_ . 'tab WHERE id_parent = ' . (int) $parent['id_parent'] . ' ORDER BY IF(class_name IN ("AdminHome", "AdminDashboard"), 1, 2), position ASC') as $child) {
                $this->container->getDb()->Execute('UPDATE ' . _DB_PREFIX_ . 'tab SET position = ' . (int) ($i++) . ' WHERE id_tab = ' . (int) $child['id_tab'] . ' AND id_parent = ' . (int) $parent['id_parent']);
            }
        }

        $this->status = 'ok';
        $this->next = 'upgradeComplete';
        $this->logger->info($this->translator->trans('The database has been cleaned.', array(), 'Modules.Autoupgrade.Admin'));
    }
}
