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

namespace PrestaShop\Module\AutoUpgrade\Parameters;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

abstract class AbstractConfigurationValidator
{
    /** @var Translator */
    protected $translator;

    function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<array{'message': string, 'target': string}>
     */
    abstract public function validate(array $array = []): array;

    /**
     * @param string|bool $boolValue
     */
    protected function validateBool($boolValue, string $key): ?string
    {
        if (!is_bool($boolValue) && ($boolValue === '' || filter_var($boolValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null)) {
            return $this->translator->trans('Value must be a boolean for %s', [$key]);
        }

        return null;
    }
}
