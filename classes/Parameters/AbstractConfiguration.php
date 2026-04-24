<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
    const DEFAULT_VALUES = [];

    /**
     * @return mixed
     */
    protected function getDefaultValue(string $const)
    {
        return static::DEFAULT_VALUES[$const] ?? null;
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
     *
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
