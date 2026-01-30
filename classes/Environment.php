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
