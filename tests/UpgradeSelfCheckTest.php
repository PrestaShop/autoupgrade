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
     * @param string $expectedVersion
     */
    public function testPhpCompatibleVersions($currentShopVersion, $phpCompatibility)
    {
        $fakeUpgrader = new Upgrader('string', false);
        $fakeUpgrader->version_num = $currentShopVersion;
        $fakeUpgradeSelfCheck = new UpgradeSelfCheck($fakeUpgrader, 'string', 'string', 'string');

        $this->assertEquals($fakeUpgradeSelfCheck->phpCompatibleVersions(), $phpCompatibility);
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
}
