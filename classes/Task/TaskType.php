<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Task;

use InvalidArgumentException;

class TaskType
{
    const TASK_TYPE_BACKUP = 'backup';
    const TASK_TYPE_UPDATE = 'update';
    const TASK_TYPE_RESTORE = 'restore';

    const ALL_TASKS = [
        self::TASK_TYPE_BACKUP,
        self::TASK_TYPE_UPDATE,
        self::TASK_TYPE_RESTORE,
    ];

    /**
     * @return self::TASK_TYPE_*
     */
    public static function fromString(string $type)
    {
        if (!in_array($type, TaskType::ALL_TASKS)) {
            throw new InvalidArgumentException('Unknown log type ' . $type);
        }

        return $type;
    }
}
