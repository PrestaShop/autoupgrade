<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use Db;

class Database
{
    /**
     * @var Db
     */
    private $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    /**
     * @return array<string, string>
     */
    public function getAllTables(): array
    {
        $tables = $this->db->executeS('SHOW TABLES LIKE "' . _DB_PREFIX_ . '%"', true, false);

        $all_tables = [];
        foreach ($tables as $v) {
            $table = reset($v);
            $all_tables[$table] = $table;
        }

        return $all_tables;
    }

    /**
     * ToDo: Send tables list instead.
     *
     * @param string[] $tablesToClean
     */
    public function cleanTablesAfterBackup(array $tablesToClean): void
    {
        foreach ($tablesToClean as $table) {
            $this->db->execute('DROP TABLE IF EXISTS `' . bqSql($table) . '`');
            $this->db->execute('DROP VIEW IF EXISTS `' . bqSql($table) . '`');
        }
        $this->db->execute('SET FOREIGN_KEY_CHECKS=1');
    }
}
