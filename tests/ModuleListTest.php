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

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Marketplace\MarketplaceClientInterface;
use PrestaShop\Module\AutoUpgrade\Marketplace\ModuleList;

class MarketplaceClientMock implements MarketplaceClientInterface
{
    public function getNativesModules($version)
    {
        $content = json_decode(file_get_contents(__DIR__ . '/fixtures/marketplace/modules-' . $version . '.txt'), true);
        return $content['modules'];
    }
}

class ModuleListTest extends TestCase
{
    public function testSameVersion()
    {
        $moduleList = new ModuleList(new MarketplaceClientMock());
        $modules = $moduleList->compareNativeModuleLists('1.7.5.0', '1.7.5.0');

        $this->assertSame([], $modules['new']);
        $this->assertSame([], $modules['deleted']);
        $this->assertFalse(empty($modules['common']));
    }

    public function testNewModules()
    {
        $moduleList = new ModuleList(new MarketplaceClientMock());
        $modules = $moduleList->compareNativeModuleLists('1.7.4.0', '1.7.5.0');


        $newModule = reset($modules['new']);
        $deletedModule = reset($modules['deleted']);

        $this->assertSame(1, count($modules['deleted']));
        $this->assertSame(1, count($modules['new']));
        $this->assertSame('ps_mbo', $newModule['name']);
        $this->assertSame('billriantpay', $deletedModule['name']);
        $this->assertFalse(empty($modules['common']));
    }

    public function testNewModulesComeFromPS()
    {
        $moduleList = new ModuleList(new MarketplaceClientMock());
        $modules = $moduleList->compareNativeModuleLists('1.6.1.23', '1.7.5.0');

        foreach ($modules['new'] as $module) {
            var_dump($module['name']);
            $this->assertSame('PrestaShop', $module['author'], sprintf('Author of module %s is not PrestaShop', $module['name']));
        }
    }
}