<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace unit\Log;

use NullLogger;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Log\Logger;

class LoggerTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        require_once __DIR__ . '/Mock/NullLogger.php';
    }

    public function testLastInfoIsRegistered()
    {
        $fd = fopen('php://temp', 'w+');

        $logger = new NullLogger($fd);
        $logger->log(Logger::INFO, 'Hello');

        rewind($fd);

        $contents = stream_get_contents($fd);
        fclose($fd);

        $this->assertStringEndsWith(
            "INFO - LoggerTest - Hello\n",
            $contents
        );
    }
}
