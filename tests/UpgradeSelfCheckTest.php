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

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Upgrader;
use PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck;

class UpgradeSelfCheckTest extends TestCase
{
    /**
     * @dataProvider provideTestPhpCompatibleVersions
     *
     * @param string $currentShopVersion
     * @param array $phpCompatibility
     */
    public function testPhpCompatibleVersions($currentShopVersion, $phpCompatibility)
    {
        $fakeUpgradeSelfCheck = $this->getFakeUpgrader($currentShopVersion);

        $this->assertEquals($fakeUpgradeSelfCheck->phpCompatibleVersions(), $phpCompatibility);
    }

    /**
     * @dataProvider provideTestIsPhpCompatible
     *
     * @param string $currentShopVersion
     * @param string $phpVersionFullName
     * @param bool $expectedResult
     */
    public function testIsPhpCompatible($currentShopVersion, $phpVersionFullName, $expectedResult)
    {
        $fakeUpgradeSelfCheck = $this->getFakeUpgrader($currentShopVersion);

        $this->assertEquals($fakeUpgradeSelfCheck->isPhpCompatible($phpVersionFullName), $expectedResult);
    }

    public function provideTestPhpCompatibleVersions()
    {
        return [
            ['1.7.0.7', ['5.4', '7.1']],
            ['1.7.1.11', ['5.4', '7.1']],
            ['1.7.2.9', ['5.4', '7.1']],
            ['1.7.3.1', ['5.4', '7.1']],
            ['1.7.4.143', ['5.6', '7.1']],
            ['1.7.5.0', ['5.6', '7.2']],
            ['1.7.6.7', ['5.6', '7.2']],
            ['1.7.7.9', ['7.1', '7.3']],
            ['1.7.8.1', ['7.1', '7.4']],
            ['8.0', ['7.2', '8.0']],
            ['8.13', ['7.2', '8.0']],
            ['1.6.1.18', ['5.2', '7.1']],
            ['1.6.1', []],
            ['1.6.1.5', []],
        ];
    }

    public function provideTestIsPhpCompatible()
    {
        return [
            ['1.6.1.18', '5.3.6-13ubuntu3.2', true], // test correct
            ['1.6.1.18', '7.3.4', false], // test false => php version too high
            ['1.7.0', '5.3.4', false], // test false => php version too low
            ['1.7.0', '5.4.4-random-string-but-correct', true], // test php version with anything in the name
            ['1.7.0', 'wrong-format', false], // test wrong format
            ['8.0', '7.2.4', true], // test correct with a two digit PrestaShop version
            ['8.0', '8.1', false], // test false with a two digit PrestaShop version
        ];
    }

    /**
     * @param string $currentShopVersion
     *
     * @return UpgradeSelfCheck
     */
    private function getFakeUpgrader($currentShopVersion)
    {
        $fakeUpgrader = new Upgrader('string', false);
        $fakeUpgrader->version_num = $currentShopVersion;

        return new UpgradeSelfCheck($fakeUpgrader, 'string', 'string', 'string');
    }
}
