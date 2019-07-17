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
use PrestaShop\Module\AutoUpgrade\Client\ModuleDetailsClient;

class ModuleDetailsClientTest extends TestCase
{
    public function testModuleVersionForPS17500()
    {
        $psVersion = '1.5.0.0';

        $this->assertSame('1.6.8', $this->getAvailableModuleVersion($psVersion));
    }

    public function testModuleVersionForPS1752()
    {
        $psVersion = '1.7.5.2';

        $this->assertSame('4.9.0', $this->getAvailableModuleVersion($psVersion));
    }

    protected function getAvailableModuleVersion($psVersion)
    {
        $clientMock = $this->getMockBuilder(ModuleDetailsClient::class)
            ->setConstructorArgs([$psVersion])
            ->setMethods([
                'call',
            ])
            ->getMock();

        $clientMock->expects($this->any())
            ->method('call')
            ->willReturn(json_decode(file_get_contents(__DIR__ . '/fixtures/api-addons-module-details-for-ps-'. $psVersion.'.json')));

        return $clientMock->getVersion();
    }
}