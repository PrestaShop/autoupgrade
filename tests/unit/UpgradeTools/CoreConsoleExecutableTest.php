<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace unit\UpgradeTools;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Exceptions\CommandLineException;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreConsoleExecutable;

class CoreConsoleExecutableTest extends TestCase
{
    private $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/prestashop_test_' . uniqid();
        mkdir($this->tempDir . '/bin', 0777, true);

        // In the context of unit tests, we obviously don't have access to PrestaShop's bin/console.
        // Let's create a fake console file that exits 0
        $consoleFile = $this->tempDir . '/bin/console';
        file_put_contents($consoleFile, "#!/usr/bin/env php\n<?php\necho 'wololo';\nexit(0);");
        chmod($consoleFile, 0755);
    }

    protected function tearDown(): void
    {
        unlink($this->tempDir . '/bin/console');
    }

    public function testCallCommandWorks(): void
    {
        $coreConsole = new CoreConsoleExecutable($this->tempDir);
        $result = $coreConsole->callCommand('assets:install');

        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('returnCode', $result);
        $this->assertEquals(0, $result['returnCode']);

        $this->assertSame([
            'returnCode' => 0,
            'output' => ['wololo'],
        ], $result);
    }

    public function testCallCommandFails(): void
    {
        $this->expectException(CommandLineException::class);

        // Create an environment where console is not found
        $badDir = sys_get_temp_dir() . '/' . uniqid();
        mkdir($badDir . '/bin', 0777, true);
        $consoleFile = $badDir . '/bin/console';

        // Any other issue like the missing permission flag could work too, but displays a text in the output.
        file_put_contents($consoleFile, "#!/usr/bin/env php\n<?php\necho 'wololo';\nexit(1);");
        chmod($consoleFile, 0755);

        $coreConsole = new CoreConsoleExecutable($badDir);
        $coreConsole->callCommand('assets:install');

        unlink($badDir . '/bin/console');
    }
}
