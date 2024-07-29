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

class BacklogTest extends TestCase
{
    public function testInitializationOfBacklogs()
    {
        $shoppingList = ['🍌🍌', '🍊🍊🍊', '🦄', '🫕'];
        $numberOfDifferentThingsToBuy = 4;

        // 1- From constructor
        $instance1 = new Backlog($shoppingList, $numberOfDifferentThingsToBuy);

        // 2- From static method
        $instance2 = Backlog::fromContents($instance1->dump());

        $this->assertSame([
            'backlog' => ['🍌🍌', '🍊🍊🍊', '🦄', '🫕'],
            'initialTotal' => 4,
        ], $instance1->dump());

        $this->assertSame([
            'backlog' => ['🍌🍌', '🍊🍊🍊', '🦄', '🫕'],
            'initialTotal' => 4,
        ], $instance2->dump());
    }

    public function testManipulationOfBacklog()
    {
        $shoppingList = ['🍌🍌', '🍊🍊🍊', '🦄', '🫕'];
        $numberOfDifferentThingsToBuy = 4;

        $backlog = new Backlog($shoppingList, $numberOfDifferentThingsToBuy);

        $this->assertSame([
            'backlog' => ['🍌🍌', '🍊🍊🍊', '🦄', '🫕'],
            'initialTotal' => 4,
        ], $backlog->dump());
        $this->assertSame(4, $backlog->getRemainingTotal());
        $this->assertSame(4, $backlog->getInitialTotal());

        $nextToBuy = $backlog->getNext();
        $this->assertSame([
            'backlog' => ['🍌🍌', '🍊🍊🍊', '🦄'],
            'initialTotal' => 4,
        ], $backlog->dump());
        $this->assertSame('🫕', $nextToBuy);
        $this->assertSame(3, $backlog->getRemainingTotal());
        $this->assertSame(4, $backlog->getInitialTotal());

        $nextToBuy = $backlog->getNext();
        $this->assertSame([
            'backlog' => ['🍌🍌', '🍊🍊🍊'],
            'initialTotal' => 4,
        ], $backlog->dump());
        $this->assertSame('🦄', $nextToBuy);
        $this->assertSame(2, $backlog->getRemainingTotal());
        $this->assertSame(4, $backlog->getInitialTotal());

        $nextToBuy = $backlog->getNext();
        $this->assertSame([
            'backlog' => ['🍌🍌'],
            'initialTotal' => 4,
        ], $backlog->dump());
        $this->assertSame('🍊🍊🍊', $nextToBuy);
        $this->assertSame(1, $backlog->getRemainingTotal());
        $this->assertSame(4, $backlog->getInitialTotal());

        $nextToBuy = $backlog->getNext();
        $this->assertSame([
            'backlog' => [],
            'initialTotal' => 4,
        ], $backlog->dump());
        $this->assertSame('🍌🍌', $nextToBuy);
        $this->assertSame(0, $backlog->getRemainingTotal());
        $this->assertSame(4, $backlog->getInitialTotal());

        $nextToBuy = $backlog->getNext();
        $this->assertSame([
            'backlog' => [],
            'initialTotal' => 4,
        ], $backlog->dump());
        $this->assertSame(null, $nextToBuy);
        $this->assertSame(0, $backlog->getRemainingTotal());
        $this->assertSame(4, $backlog->getInitialTotal());
    }
}
