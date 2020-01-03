<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Log\LegacyLogger;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader17;

class CoreUpgraderTest extends TestCase
{
    protected $coreUpgrader;

    protected function setUp()
    {
        parent::setUp();

        $stub = $this->getMockBuilder(UpgradeContainer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->coreUpgrader = new CoreUpgrader17($stub, new LegacyLogger());
    }

    /**
     * @dataProvider versionProvider
     */
    public function testVersionNormalization($source, $expected)
    {
        $this->assertSame($expected, $this->coreUpgrader->normalizeVersion($source));
    }

    public function versionProvider()
    {
        return array(
            array('1.7', '1.7.0.0'),
            array('1.7.2', '1.7.2.0'),
            array('1.6.1.0-beta', '1.6.1.0-beta'),
            array('1.6.1-beta', '1.6.1-beta.0'), // Weird, but still a test
        );
    }
}
