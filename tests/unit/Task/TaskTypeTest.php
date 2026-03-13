<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;

class TaskTypeTest extends TestCase
{
    public function testFromStringReturnsValidTaskType()
    {
        $this->assertSame(TaskType::TASK_TYPE_BACKUP, TaskType::fromString('backup'));
        $this->assertSame(TaskType::TASK_TYPE_UPDATE, TaskType::fromString('update'));
        $this->assertSame(TaskType::TASK_TYPE_RESTORE, TaskType::fromString('restore'));
    }

    public function testFromStringThrowsExceptionForInvalidTaskType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown log type invalid_task');

        TaskType::fromString('invalid_task');
    }
}
