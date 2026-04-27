<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Progress\Backlog;
use PrestaShop\Module\AutoUpgrade\Progress\CompletionCalculator;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreFiles;
use PrestaShop\Module\AutoUpgrade\Task\Runner\SingleTask;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateFiles;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateInitialization;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateModules;

class CompletionCalculatorTest extends TestCase
{
    public function testRetrievalOfBasePercentages()
    {
        $completionCalculator = new CompletionCalculator();

        $this->assertSame(0, $completionCalculator->getBasePercentageOfTask(UpdateInitialization::class));
        $this->assertSame(89, $completionCalculator->getBasePercentageOfTask(UpdateModules::class));
        $this->assertSame(33, $completionCalculator->getBasePercentageOfTask(RestoreFiles::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(SingleTask::class . ' has no percentage. Make sure to send an upgrade, backup or restore task.');
        $completionCalculator->getBasePercentageOfTask(SingleTask::class);
    }

    public function testComputationOfPercentages()
    {
        $completionCalculator = new CompletionCalculator();

        $backlog = new Backlog(['stuff', 'stuff', 'stuff'], 3);

        $this->assertSame(
            24,
            $completionCalculator->computePercentage($backlog, UpdateFiles::class, UpdateDatabase::class)
        );

        $backlog->getNext();

        $this->assertSame(
            36,
            $completionCalculator->computePercentage($backlog, UpdateFiles::class, UpdateDatabase::class)
        );

        $backlog->getNext();

        $this->assertSame(
            48,
            $completionCalculator->computePercentage($backlog, UpdateFiles::class, UpdateDatabase::class)
        );

        $backlog->getNext();

        $this->assertSame(
            60,
            $completionCalculator->computePercentage($backlog, UpdateFiles::class, UpdateDatabase::class)
        );
    }

    public function testComputationOfPercentagesOfEmptyBacklog()
    {
        $completionCalculator = new CompletionCalculator();

        $backlog = new Backlog([], 0);

        $this->assertSame(
            60,
            $completionCalculator->computePercentage($backlog, UpdateFiles::class, UpdateDatabase::class)
        );
    }
}
