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
     * @var array<string, string> Indexed by relative path from root folder, value is the symbolic link target
     */
    private $symbolicLinks;

    /**
     * @param mixed[] $backlog
     * @param int $initialTotal
     * @param array<string, string> $symbolicLinks
     */
    public function __construct(array $backlog, int $initialTotal, array $symbolicLinks = [])
    {
        $this->backlog = $backlog;
        $this->initialTotal = $initialTotal;
        $this->symbolicLinks = $symbolicLinks;
    }

    /**
     * @param array{'backlog':mixed[],'initialTotal':int, 'symbolicLinks':array<string, string>} $contents
     */
    public static function fromContents($contents): self
    {
        return new self($contents['backlog'], $contents['initialTotal'], $contents['symbolicLinks']);
    }

    /**
     * @return array{'backlog':mixed[],'initialTotal':int, 'symbolicLinks':array<string, string>}
     */
    public function dump(): array
    {
        return [
            'backlog' => $this->backlog,
            'initialTotal' => $this->initialTotal,
            'symbolicLinks' => $this->symbolicLinks,
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

    /**
     * @return array<string, string>
     */
    public function getSymbolicLinks(): array
    {
        return $this->symbolicLinks;
    }
}
