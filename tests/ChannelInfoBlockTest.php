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
use PrestaShop\Module\AutoUpgrade\ChannelInfo;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Twig\Block\ChannelInfoBlock;
use PrestaShop\Module\AutoUpgrade\Upgrader;

class ChannelInfoBlockTest extends TestCase
{
    /**
     * @dataProvider provideTestIsCurrentPrestashopVersion
     *
     * @param string $prestaVersion
     * @param string $currentPrestaVersion
     * @param bool $result
     */
    public function testIsCurrentPrestashopVersion($prestaVersion, $currentPrestaVersion, $result)
    {
        $channelInfoBlock = $this->getChannelInfoBlock($prestaVersion);

        $this->assertEquals($result, $channelInfoBlock->isCurrentPrestashopVersion($prestaVersion, $currentPrestaVersion));
    }

    /**
     * @dataProvider provideTestBuildPhpVersionsList
     *
     * @param array $phpVersionRange
     * @param array $result
     */
    public function testBuildPhpVersionsList($phpVersionRange, $result)
    {
        $channelInfoBlock = $this->getChannelInfoBlock('1.7.7'); // version not relevant for this test

        $this->assertEquals($result, $channelInfoBlock->buildPhpVersionsList($phpVersionRange));
    }

    public function testBuildPhpVersionsListException()
    {
        $channelInfoBlock = $this->getChannelInfoBlock('1.7.7'); // version not relevant for this test
        $this->expectException(InvalidArgumentException::class);

        $channelInfoBlock->buildPhpVersionsList('invalid argument');
    }

    /**
     * @dataProvider provideTestBuildPSLabel
     *
     * @param string $startVersion
     * @param string $endVersion
     * @param string $result
     */
    public function testBuildPSLabel($startVersion, $endVersion, $result)
    {
        $channelInfoBlock = $this->getChannelInfoBlock('1.7.7'); // version not relevant for this test

        $this->assertEquals($result, $channelInfoBlock->buildPSLabel($startVersion, $endVersion));
    }

    /**
     * Provider for testBuildPSLabel
     *
     * @return string[][]
     */
    public function provideTestBuildPSLabel()
    {
        return [
            ['1.6.1.18', 'whatever', '>= 1.6.1.18'], // special case for minimal version (second value doesn't matter)
            ['1.7.0', '1.7.3', '1.7.0 ~ 1.7.3'], // versions are concatenated with ~
        ];
    }

    /**
     * Provider for testBuildPhpVersionsList
     *
     * @return string[][][]
     */
    public function provideTestBuildPhpVersionsList()
    {
        return [
            [
                ['5.2', '7.1'],
                ['5.2', '5.3', '5.4', '5.5', '5.6', '7.0', '7.1'],
            ],
            [
                ['5.4', '7.1'],
                ['5.4', '5.5', '5.6', '7.0', '7.1'],
            ],
            [
                ['5.6', '7.1'],
                ['5.6', '7.0', '7.1'],
            ],
            [
                ['7.1', '7.3'],
                ['7.1', '7.2', '7.3'],
            ],
            [
                ['7.1', '7.4'],
                ['7.1', '7.2', '7.3', '7.4'],
            ],
            [
                ['7.2', '8.0'],
                ['7.2', '7.3', '7.4', '8.0'],
            ],
        ];
    }

    /**
     * Provider for testIsCurrentPrestashopVersion
     *
     * @return array[]
     */
    public function provideTestIsCurrentPrestashopVersion()
    {
        return [
            ['1.7.0', '1.6.0', false], // test version below minimal version (cannot match)
            ['1.7.7', '1.7.7', true], // test exact match (must match)
            ['1.7.7', '1.7.7.4', true], // test patch version (must match)
            ['1.7.7', '1.7.6', false], // test lower minor version (must not match)
            ['1.7.7', '1.7.8', false], // test higher minor version (must not match)
            ['1.7.6', '1.6.7', false], // test lower major version (must not match)
            ['1.7.6', '1.8.7', false], // test higher major version (must not match)
            ['8.0', '8.0.1', true], // test match with a 2 digit version, and same version with a patch
            ['8.1', '8.0.1', false], // test lower minor version with a 2 digit version
            ['8.0', '8.1.1', false], // test higher minor version with a 2 digit version
            ['9.1', '8.1.1', false], // test lower major version with a 2 digit version
        ];
    }

    /**
     * @param string $currentShopVersion
     *
     * @return ChannelInfoBlock
     */
    private function getChannelInfoBlock($currentShopVersion)
    {
        $fakeUpgrader = new Upgrader('string', false);
        $fakeUpgrader->version_num = $currentShopVersion;

        $channelInfo = $this->getMockBuilder(ChannelInfo::class)->disableOriginalConstructor()->getMock();

        return new ChannelInfoBlock(new UpgradeConfiguration(), $channelInfo, new Twig_Environment());
    }
}
