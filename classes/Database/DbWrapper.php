<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
