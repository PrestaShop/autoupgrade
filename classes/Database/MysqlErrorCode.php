<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Database;

class MysqlErrorCode
{
    public const TABLE_ALREADY_EXISTS = '1050';
    public const UNKNOWN_COLUMN_IN_FIELD_LIST = '1054';
    public const DUPLICATE_COLUMN_NAME = '1060';
    public const DUPLICATE_KEY = '1061';
    public const DUPLICATE_ENTRY = '1062';
    public const CANNOT_DROP_KEY = '1091';
}
