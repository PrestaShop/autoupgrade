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

class VersionUtils
{
    const MODULE_COMPATIBLE_PHP_VERSION = 70100;

    public static function getHumanReadableVersionOf($versionInt)
    {
        $major = intdiv($versionInt, 10000);
        $minor = intdiv($versionInt % 10000, 100);

        return sprintf('%d.%d', $major, $minor);
    }

    public static function getPhpVersionId($version)
    {
        $parts = explode('.', $version);

        $major = isset($parts[0]) ? (int) $parts[0] : 0;
        $minor = isset($parts[1]) ? (int) $parts[1] : 0;
        $patch = isset($parts[2]) ? (int) $parts[2] : 0;

        return $major * 10000 + $minor * 100 + $patch;
    }

    public static function getPhpMajorMinorVersionId()
    {
        $phpVersionId = PHP_VERSION_ID;

        $major = (int) ($phpVersionId / 10000);
        $minor = (int) (($phpVersionId % 10000) / 100);

        return $major * 10000 + $minor * 100;
    }

    public static function isActualPHPVersionCompatible()
    {
        return PHP_VERSION_ID >= self::MODULE_COMPATIBLE_PHP_VERSION;
    }

    public static function getPrestashopMinorVersion($version)
    {
        $parts = explode('.', $version);
        $versionString = implode('.', $parts);
        if (strlen($versionString) >= 8) {
            $minorVersionParts = array_slice($parts, 0, 3);
        } else {
            $minorVersionParts = array_slice($parts, 0, 2);
        }

        return implode('.', $minorVersionParts);
    }
}
