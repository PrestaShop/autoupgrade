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

    public function testClassIgnoresRandomData()
    {
        $state = new State();
        $state->importFromArray([
            'wow' => 'epic',
            'backupName' => 'doge'
        ]);
        $exported = $state->export();

        $this->assertArrayNotHasKey('wow', $exported);
        $this->assertSame('doge', $exported['backupName']);
    }

    // Tests with encoded data


    public function testClassReceivesPropertyFromEncodedData()
    {
        $encodedData = base64_encode(json_encode(['backupName' => 'doge']));
        $state = new State();
        $state->importFromEncodedData($encodedData);
        $exported = $state->export();

        $this->assertSame('doge', $state->getBackupName());
        $this->assertSame('doge', $exported['backupName']);
    }
}