<?php
/*
 * 2007-2018 PrestaShop
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
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Requirement\PhpCompatibility;

class PhpCompatibilityTest extends TestCase
{
    /**
     * @dataProvider phpChecksDataProvider
     */
    public function testLastInfoIsRegistered($phpVersion, $prestashopVersion, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            (new PhpCompatibility())->versionsAreCompatible($phpVersion, $prestashopVersion)
        );
    }

    public function phpChecksDataProvider()
    {
        return [
            // Based on data we have, we can consider that older versions
            // of PHP are running with older version of PS
            ['5.2', '1.5', true],
            ['7.2', '1.5', false],

            // Data explicitely given for PS 1.6.1
            ['5.2', '1.6.1.0', true],
            ['7.1', '1.6.1.0', true],
            ['7.1.34', '1.6.1.0', true],
            ['5.1', '1.6.1.0', false],
            ['7.2', '1.6.1.0', false],

            // Data explicitely given for PS 1.7.0 -> 1.7.3
            ['5.2', '1.7.0', false],
            ['5.1', '1.7.1', false],
            ['5.3.99.99', '1.7.2', false],
            ['7.2', '1.7.3', false],
            ['7.2', '1.7.1', false],
            ['7.99', '1.7.1', false],
            ['7.2-doge', '1.7.2', false],
            ['5.4.0.0', '1.7.3', true],
            ['5.5', '1.7.3', true],
            ['5.6', '1.7.3', true],
            ['7.0', '1.7.3.24', true],
            ['7.1', '1.7.3', true],

            // Data explicitely given for PS 1.7.4
            ['5.6', '1.7.4.0', true],
            ['7.1.34', '1.7.4.98', true],
            ['7.2', '1.7.4.0-beta', false],

            // Data explicitely given for PS 1.7.5 -> 1.7.6
            ['5.6', '1.7.5.0', true],
            ['7.1.34', '1.7.6.98', true],
            ['7.2', '1.7.6.0-beta', true],
            ['5.5', '1.7.6.0-beta', false],

            // Based on data we have, we can consider that newer versions
            // of PHP are running with newer version of PS
            ['7.89', '1.7.7', true],
            ['5.5', '1.7.7.0-beta', false],
            ['5.6', '1.7.7.0-beta', true],
        ];
    }
}
