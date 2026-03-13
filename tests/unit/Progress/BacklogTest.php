<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
