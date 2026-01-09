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

namespace PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace;

class Attributes
{
    /** @var string[] */
    public $groups = [];
    /** @var string[] */
    public $combinations = [];

    /**
     * @param array{
     *     groups?: array<int, string>,
     *     combinations?: array<int, string>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->groups = $data['groups'] ?? [];
        $obj->combinations = $data['combinations'] ?? [];

        return $obj;
    }
}
