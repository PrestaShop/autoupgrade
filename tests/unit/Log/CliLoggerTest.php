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

namespace unit\Log;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Log\CliLogger;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use Symfony\Component\Console\Output\ConsoleOutput;

class CliLoggerTest extends TestCase
{
    /**
     * @dataProvider filtersProvider
     */
    public function testFiltersProperlyApplied($level, $filterLevel, $expected)
    {
        $output = new ConsoleOutput();
        $logger = new CliLogger($output);
        $logger->setFilter($filterLevel);
        $this->assertSame($expected, $logger->isFiltered($level));
    }

    public function filtersProvider()
    {
        return [
            [Logger::EMERGENCY, Logger::INFO, false],
            [Logger::INFO, Logger::EMERGENCY, true],
            [Logger::ERROR, Logger::ERROR, false],
            [Logger::ERROR, Logger::WARNING, false],
            [Logger::ERROR, Logger::CRITICAL, true],
        ];
    }

    public function testLastInfoIsRegistered()
    {
        $output = new ConsoleOutput();
        $logger = new CliLogger($output);
        $logger->log(Logger::INFO, 'Hello');

        $this->assertSame('INFO - Hello', $logger->getLastInfo());
    }

    public function testSensitiveDataAreReplaced()
    {
        $output = new ConsoleOutput();
        $logger = new CliLogger($output);
        $logger->setSensitiveData([
            'my-aldmin-folder' => '******',
            '🚬' => '🚭',
            'some@email.com' => '***@****.**',
        ]);

        $this->assertSame(
            'File /shop/******/config.yml created',
            $logger->cleanFromSensitiveData('File /shop/my-aldmin-folder/config.yml created')
        );

        $this->assertSame(
            '***@****.** suggested 🚭',
            $logger->cleanFromSensitiveData('some@email.com suggested 🚬')
        );
    }
}
