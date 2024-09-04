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

namespace PrestaShop\Module\AutoUpgrade;

use InvalidArgumentException;

class VersionUtils
{
    const MODULE_COMPATIBLE_PHP_VERSION = 70100;

    /**
     * @param int $versionInt
     *
     * @return string
     */
    public static function getHumanReadableVersionOf($versionInt)
    {
        if (!is_int($versionInt)) {
            throw new InvalidArgumentException('Version must be an integer.');
        }

        if ($versionInt < 0) {
            throw new InvalidArgumentException('Version cannot be negative.');
        }

        if ($versionInt < 10000 || $versionInt > 99999) {
            throw new InvalidArgumentException('Version must be a five-digit integer.');
        }

        $major = intdiv($versionInt, 10000);
        $minor = intdiv($versionInt % 10000, 100);
        $patch = $versionInt % 100;

        return sprintf('%d.%d.%d', $major, $minor, $patch);
    }

    /**
     * @param string $version
     *
     * @return int
     */
    public static function getPhpVersionId($version)
    {
        if (!is_string($version)) {
            throw new InvalidArgumentException('Version must be a string.');
        }

        $version = trim($version);
        if (empty($version)) {
            throw new InvalidArgumentException('Version string cannot be empty.');
        }

        $parts = explode('.', $version);

        $versionNumbers = [0, 0, 0];

        foreach ($parts as $index => $part) {
            if (!is_numeric($part)) {
                throw new InvalidArgumentException('Version parts must be numeric.');
            }
            $versionNumbers[$index] = (int) $part;
        }

        list($major, $minor, $patch) = $versionNumbers;

        return $major * 10000 + $minor * 100 + $patch;
    }

    /**
     * @param int $phpVersionId
     *
     * @return int
     */
    public static function getPhpMajorMinorVersionId($phpVersionId)
    {
        $major = (int) ($phpVersionId / 10000);
        $minor = (int) (($phpVersionId % 10000) / 100);

        return $major * 10000 + $minor * 100;
    }

    /**
     * @return bool
     */
    public static function isActualPHPVersionCompatible()
    {
        return PHP_VERSION_ID >= self::MODULE_COMPATIBLE_PHP_VERSION;
    }

    /**
     * @param string $version
     *
     * @return array{'major': string,'minor': string,'patch': string}
     */
    public static function splitPrestaShopVersion($version)
    {
        if (!is_string($version)) {
            throw new InvalidArgumentException('Version must be a string.');
        }

        $version = trim($version);
        if (empty($version)) {
            throw new InvalidArgumentException('Version string cannot be empty.');
        }

        preg_match(
            '#^(?<patch>(?<minor>(?<major>([0-1]{1}\.[0-9]+)|([0-9]+))(?:\.[0-9]+){1})(?:\.[0-9]+){1})$#',
            $version,
            $matches
        );

        if (empty($matches)) {
            throw new InvalidArgumentException('Invalid version format. Expected format: X.Y.Z or X.Y.Z.W');
        }

        return [
            'major' => $matches['major'],
            'minor' => $matches['minor'],
            'patch' => $matches['patch'],
        ];
    }
}
