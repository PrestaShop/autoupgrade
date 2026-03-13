<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
