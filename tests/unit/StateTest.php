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
    }

    public function testGetRestoreVersion()
    {
        $state = new State();

        $this->assertSame(
            '1.7.8.11',
            $state->setRestoreName('V1.7.8.11_20240604-170048-3ceb32b2')
                ->getRestoreVersion()
        );

        $this->assertSame(
            '8.1.6',
            $state->setRestoreName('V8.1.6_20240604-170048-3ceb32b2')
                ->getRestoreVersion()
        );
    }

    public function testProgressionValue()
    {
        $state = new State();
        $this->assertSame(null, $state->getProgressPercentage());

        $state->setProgressPercentage(0);
        $this->assertSame(0, $state->getProgressPercentage());

        $state->setProgressPercentage(55);
        $this->assertSame(55, $state->getProgressPercentage());

        // Percentage cannot go down, an exception will be thrown
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Updated progress percentage cannot be lower than the currently set one.');

        $state->setProgressPercentage(10);
    }
}
