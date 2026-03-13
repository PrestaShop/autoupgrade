<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade;

class Environment
{
    public const URL_TRACKING_ENV_NAME = 'PS_URL_TRACKING';

    /**
     * Gets the value of an environment variable.
     * It checks for the variable in $_SERVER first, then falls back to getenv().
     *
     * @param string $envName the name of the environment variable
     *
     * @return mixed|null the value of the environment variable, or null if not found
     */
    public function getEnvValue(string $envName)
    {
        $envValue = null;

        if (isset($_SERVER[$envName])) {
            $envValue = $_SERVER[$envName];
        // If the variable is defined, we will get a string back with the getEnv function; if false is returned, the variable was not found.
        } elseif (getenv($envName) !== false) {
            $envValue = getenv($envName);
        }

        return $envValue;
    }

    public function getBoolean(string $envName, bool $default = false): bool
    {
        $value = $this->getEnvValue($envName);

        if (null === $value) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}
