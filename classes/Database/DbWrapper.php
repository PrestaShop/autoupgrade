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

namespace PrestaShop\Module\AutoUpgrade\Database;

use Db;
use mysqli_result;
use PDOStatement;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException;

class DbWrapper
{
    /**
     * @param array<mixed> $data
     *
     * @throws UpdateDatabaseException
     */
    public static function update(string $table, array $data, string $where = '', int $limit = 0, bool $null_values = false, bool $use_cache = true, bool $add_prefix = true): bool
    {
        $result = Db::getInstance()->update($table, $data, $where, $limit, $null_values, $use_cache, $add_prefix);
        self::validateDBQuerySuccess();

        return $result;
    }

    /**
     * @throws UpdateDatabaseException
     */
    public static function execute(string $sql, bool $use_cache = true): bool
    {
        $result = Db::getInstance()->execute($sql, $use_cache);
        self::validateDBQuerySuccess();

        return $result;
    }

    /**
     * @throws UpdateDatabaseException
     *
     * @return array<mixed>|bool|mysqli_result|PDOStatement|resource|null
     */
    public static function executeS(string $sql, bool $array = true, bool $use_cache = true)
    {
        $result = Db::getInstance()->executeS($sql, $array, $use_cache);
        self::validateDBQuerySuccess();

        return $result;
    }

    /**
     * @throws UpdateDatabaseException
     *
     * @return string|false|null Returns false if no results
     */
    public static function getValue(string $sql, bool $use_cache = true)
    {
        $result = Db::getInstance()->getValue($sql, $use_cache);
        self::validateDBQuerySuccess();

        return $result;
    }

    /**
     * @param array<mixed> $data
     *
     * @throws UpdateDatabaseException
     */
    public static function insert(string $table, array $data, bool $null_values = false, bool $use_cache = true, int $type = Db::INSERT, bool $add_prefix = true): bool
    {
        $result = Db::getInstance()->insert($table, $data, $null_values, $use_cache, $type, $add_prefix);
        self::validateDBQuerySuccess();

        return $result;
    }

    /**
     * @return int|string
     *
     * @throws UpdateDatabaseException
     */
    public static function Insert_ID()
    {
        $result = Db::getInstance()->Insert_ID();
        self::validateDBQuerySuccess();

        return $result;
    }

    /**
     * @throws UpdateDatabaseException
     */
    public static function delete(string $table, string $where = '', int $limit = 0, bool $use_cache = true, bool $add_prefix = true): bool
    {
        $result = Db::getInstance()->delete($table, $where, $limit, $use_cache, $add_prefix);
        self::validateDBQuerySuccess();

        return $result;
    }

    /**
     * @throws UpdateDatabaseException
     *
     * @return array<mixed>|bool|object|null
     */
    public static function getRow(string $sql, bool $use_cache = true)
    {
        $result = Db::getInstance()->getRow($sql, $use_cache);
        self::validateDBQuerySuccess();

        return $result;
    }

    /**
     * @throws UpdateDatabaseException
     */
    public static function validateDBQuerySuccess(): void
    {
        $previousQueryError = Db::getInstance()->getMsgError();
        $previousQueryErrorCode = Db::getInstance()->getNumberError();

        if (!empty($previousQueryError) || !empty($previousQueryErrorCode)) {
            throw new UpdateDatabaseException($previousQueryError, $previousQueryErrorCode);
        }
    }
}
