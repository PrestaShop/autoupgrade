<?php

/*
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2019 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\Requirement;

class PhpCompatibility
{
    /**
     * PHP compatibility table for PrestaShop
     *
     * @see https://devdocs.prestashop.com/1.7/basics/installation/system-requirements/
     *
     * @var array
     */
    const PRESTASHOP_PHP_COMPATIBILITY = [
        ['ps' => ['min' => '1.6.1', 'max' => '1.6.1.999'], 'php' => ['min' => '5.2', 'max' => '7.1.999']],
        ['ps' => ['min' => '1.7.0', 'max' => '1.7.3.999'], 'php' => ['min' => '5.4', 'max' => '7.1.999']],
        ['ps' => ['min' => '1.7.4', 'max' => '1.7.4.999'], 'php' => ['min' => '5.6', 'max' => '7.1.999']],
        ['ps' => ['min' => '1.7.5', 'max' => '1.7.6.999'], 'php' => ['min' => '5.6', 'max' => '7.2.999']],
        ['ps' => ['min' => '1.7.7', 'max' => '1.7.7.999'], 'php' => ['min' => '7.1', 'max' => '7.3.999']],
    ];

    /**
     * Check that the PHP version is compatible with the version we upgrade to
     *
     * @param string $phpVersion
     * @param string $prestashopVersion
     *
     * @return bool
     */
    public function versionsAreCompatible($phpVersion, $prestashopVersion)
    {
        // Check explicit compatibility ranges
        foreach (self::PRESTASHOP_PHP_COMPATIBILITY as $compatibilityData) {
            if (version_compare($prestashopVersion, $compatibilityData['ps']['min'], '<')) {
                continue;
            }
            if (version_compare($prestashopVersion, $compatibilityData['ps']['max'], '>')) {
                continue;
            }

            return version_compare($phpVersion, $compatibilityData['php']['min'], '>=')
                && version_compare($phpVersion, $compatibilityData['php']['max'], '<=');
        }

        // If not given in array, guess it:
        // - Allow older versions of PS running with older versions of PHP
        // - Allow newer versions of PS running with newer versions of PHP
        $data = self::PRESTASHOP_PHP_COMPATIBILITY;
        $firstCompatibilityGiven = reset($data);
        if (version_compare($prestashopVersion, $firstCompatibilityGiven['ps']['min'], '<=')
            && version_compare($phpVersion, $firstCompatibilityGiven['php']['max'], '<=')) {
            return true;
        }
        $lastCompatibilityGiven = end($data);
        if (version_compare($prestashopVersion, $lastCompatibilityGiven['ps']['max'], '>=')
            && version_compare($phpVersion, $lastCompatibilityGiven['php']['min'], '>=')) {
            return true;
        }

        return false;
    }
}
