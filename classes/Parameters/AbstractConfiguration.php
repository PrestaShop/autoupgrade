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

use Doctrine\Common\Collections\ArrayCollection;
use UnexpectedValueException;

/**
 * Abstract for module configurations.
 *
 * @extends ArrayCollection<string, mixed>
 */
abstract class AbstractConfiguration extends ArrayCollection
{
    /**
     * @var array<string, mixed>
     */
    const PS_CONST_DEFAULT_VALUE = [];

    /**
     * @return mixed
     */
    protected function getDefaultValue(string $const)
    {
        return static::PS_CONST_DEFAULT_VALUE[$const] ?? null;
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return void
     *
     * @throws UnexpectedValueException
     */
    public function merge(array $array = []): void
    {
        foreach ($array as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Resolves a configuration value into a boolean.
     *
     * This method attempts to retrieve the value for a given configuration key.
     * If the value is missing or not a valid boolean (e.g., an invalid string),
     * it falls back to the defined default value.
     *
     * @param string $const the configuration key to evaluate
     *
     * @return bool the resolved boolean value or the fallback default
     */
    protected function computeBooleanConfiguration(string $const): bool
    {
        $currentValue = $this->get($const);
        $defaultValue = $this->getDefaultValue($const);

        if ($currentValue === null) {
            return $defaultValue;
        }

        $currentValue = filter_var($currentValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $currentValue !== null ? $currentValue : $defaultValue;
    }

    /**
     * Resolves a configuration value into a integer.
     *
     * This method attempts to retrieve the value for a given configuration key.
     * If the value is missing or not a valid int (e.g., an invalid string),
     * it falls back to the defined default value.
     *
     * @param string $const the configuration key to evaluate
     * @return int the resolved int value or the fallback default
     */
    protected function computeIntConfiguration(string $const): int
    {
        $currentValue = $this->get($const);
        $defaultValue = $this->getDefaultValue($const);

        if ($currentValue === null) {
            return $defaultValue;
        }

        $currentValue = filter_var($currentValue, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        return $currentValue !== null ? $currentValue : $defaultValue;
    }
}
