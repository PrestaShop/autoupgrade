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
        $this->assertSame(80, $completionCalculator->getBasePercentageOfTask(UpdateModules::class));
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
            40,
            $completionCalculator->computePercentage($backlog, UpdateFiles::class, UpdateDatabase::class)
        );

        $backlog->getNext();

        $this->assertSame(
            46,
            $completionCalculator->computePercentage($backlog, UpdateFiles::class, UpdateDatabase::class)
        );

        $backlog->getNext();

        $this->assertSame(
            53,
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
