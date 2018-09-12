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
use PrestaShop\Module\AutoUpgrade\State;

class StateTest extends TestCase
{
    public function testClassReceivesProperty()
    {
        $state = new State();
        $state->importFromArray(['backupName' => 'doge']);
        $exported = $state->export();

        $this->assertSame('doge', $state->getBackupName());
        $this->assertSame('doge', $exported['backupName']);
    }

    public function testClassReceivesModulesAddonsProperty()
    {
        $modules = [
            22320 => 'ps_imageslider',
            22323 => 'ps_socialfollow',
        ];
        $state = new State();
        $state->importFromArray(['modules_addons' => $modules]);
        $exported = $state->export();

        $this->assertSame($modules, $state->getModules_addons());
        $this->assertSame($modules, $exported['modules_addons']);
    }

    public function testClassIgnoresRandomData()
    {
        $state = new State();
        $state->importFromArray([
            'wow' => 'epic',
            'backupName' => 'doge',
        ]);
        $exported = $state->export();

        $this->assertArrayNotHasKey('wow', $exported);
        $this->assertSame('doge', $exported['backupName']);
    }

    // Tests with encoded data

    public function testClassReceivesPropertyFromEncodedData()
    {
        $modules = [
            22320 => 'ps_imageslider',
            22323 => 'ps_socialfollow',
        ];
        $data = [
            'nextParams' => [
                'backupName' => 'doge',
                'modules_addons' => $modules,
            ],
        ];
        $encodedData = base64_encode(json_encode($data));
        $state = new State();
        $state->importFromEncodedData($encodedData);
        $exported = $state->export();

        $this->assertSame('doge', $state->getBackupName());
        $this->assertSame('doge', $exported['backupName']);
        $this->assertSame($modules, $state->getModules_addons());
        $this->assertSame($modules, $exported['modules_addons']);
    }
}
