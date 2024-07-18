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
