<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Progress;

class Backlog
{
    /**
     * Number of elements in backlog at the beginning
     *
     * @var int
     */
    private $initialTotal;

    /**
     * Remaining backlog of elements
     *
     * @var mixed[]
     */
    private $backlog;

    /**
     * @param mixed[] $backlog
     */
    public function __construct(array $backlog, int $initialTotal)
    {
        $this->backlog = $backlog;
        $this->initialTotal = $initialTotal;
    }

    /**
     * @param array{'backlog':mixed[],'initialTotal':int} $contents
     */
    public static function fromContents($contents): self
    {
        return new self($contents['backlog'], $contents['initialTotal']);
    }

    /**
     * @return array{'backlog':mixed[],'initialTotal':int}
     */
    public function dump(): array
    {
        return [
            'backlog' => $this->backlog,
            'initialTotal' => $this->initialTotal,
        ];
    }

    /**
     * @return mixed
     */
    public function getNext()
    {
        return array_pop($this->backlog);
    }

    public function getRemainingTotal(): int
    {
        return count($this->backlog);
    }

    public function getInitialTotal(): int
    {
        return $this->initialTotal;
    }
}
