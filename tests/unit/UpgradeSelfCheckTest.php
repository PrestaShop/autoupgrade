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

namespace unit;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck;

class UpgradeSelfCheckTest extends TestCase
{
    /** @var UpgradeSelfCheck */
    private $upgradeSelfCheck;

    public function setUp()
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }

        $this->upgradeSelfCheck = $this->getMockBuilder(UpgradeSelfCheck::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPhpCompatibilityRange'])
            ->getMock();
    }

    public function testInvalidCompatibilityRange()
    {
        $this->upgradeSelfCheck->method('getPhpCompatibilityRange')
            ->willReturn(['php_min_version' => '7.1.0', 'php_max_version' => '7.4.0']);

        $this->assertEquals(UpgradeSelfCheck::PHP_REQUIREMENTS_INVALID, $this->upgradeSelfCheck->getPhpRequirementsState(80000));
    }

    public function testValidCompatibilityRange()
    {
        $this->upgradeSelfCheck->method('getPhpCompatibilityRange')
            ->willReturn(['php_min_version' => '7.1.0', 'php_max_version' => '7.4.0']);

        $this->assertEquals(UpgradeSelfCheck::PHP_REQUIREMENTS_VALID, $this->upgradeSelfCheck->getPhpRequirementsState(70300));

        $this->upgradeSelfCheck->method('getPhpCompatibilityRange')
            ->willReturn(['php_min_version' => '7.2.5', 'php_max_version' => '8.1']);

        $this->assertEquals(UpgradeSelfCheck::PHP_REQUIREMENTS_VALID, $this->upgradeSelfCheck->getPhpRequirementsState(70213));
    }

    public function testUnknownCompatibilityRange()
    {
        $this->upgradeSelfCheck->method('getPhpCompatibilityRange')
            ->willReturn(null);

        $this->assertEquals(UpgradeSelfCheck::PHP_REQUIREMENTS_UNKNOWN, $this->upgradeSelfCheck->getPhpRequirementsState(70300));
    }
}
