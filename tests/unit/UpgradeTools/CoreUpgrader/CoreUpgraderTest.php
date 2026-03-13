<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace unit\UpgradeTools\CoreUpgrader;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader17;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class CoreUpgraderTest extends TestCase
{
    /**
     * @var CoreUpgrader17
     */
    private $coreUpgrader;

    protected function setUp()
    {
        $this->coreUpgrader = $this->getMockBuilder(CoreUpgrader17::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @throws ReflectionException
     */
    protected static function getMethod($name): ReflectionMethod
    {
        $class = new ReflectionClass(CoreUpgrader17::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @throws ReflectionException
     */
    public function testExtractPhpStringFromQueryWithoutParameter()
    {
        $method = self::getMethod('extractPhpStringFromQuery');
        $sql = '/* PHP:add_supplier_manufacturer_routes(); */;';
        $methodExtracted = $method->invokeArgs($this->coreUpgrader, [$sql]);

        $this->assertSame('add_supplier_manufacturer_routes();', $methodExtracted);
    }

    /**
     * @throws ReflectionException
     */
    public function testExtractPhpStringFromQueryWithParameter()
    {
        $method = self::getMethod('extractPhpStringFromQuery');
        $sql = '/* PHP:add_supplier_manufacturer_routes(1); */;';
        $methodExtracted = $method->invokeArgs($this->coreUpgrader, [$sql]);

        $this->assertSame('add_supplier_manufacturer_routes(1);', $methodExtracted);

        $sql = "/* PHP:add_supplier_manufacturer_routes('trotrolo'); */;";
        $methodExtracted = $method->invokeArgs($this->coreUpgrader, [$sql]);

        $this->assertSame("add_supplier_manufacturer_routes('trotrolo');", $methodExtracted);

        $sql = '/* PHP:add_supplier_manufacturer_routes("trotr\'olo\'"); */;';
        $methodExtracted = $method->invokeArgs($this->coreUpgrader, [$sql]);

        $this->assertSame('add_supplier_manufacturer_routes("trotr\'olo\'");', $methodExtracted);
    }

    /**
     * @throws ReflectionException
     */
    public function testExtractParametersAsString()
    {
        $method = self::getMethod('extractParametersAsString');
        $phpString = "bestMethodName('trololo');";
        $stringExtracted = $method->invokeArgs($this->coreUpgrader, [$phpString]);

        $this->assertSame("('trololo')", $stringExtracted);
    }

    /**
     * @throws ReflectionException
     */
    public function testExtractParametersFromPhpString()
    {
        $method = self::getMethod('extractParametersFromString');
        $phpString = "('jack')";
        $parametersExtracted = $method->invokeArgs($this->coreUpgrader, [$phpString]);

        $this->assertSame(['jack'], $parametersExtracted);

        $method = self::getMethod('extractParametersFromString');
        $phpString = "('jack', [1,2,3,4])";
        $parametersExtracted = $method->invokeArgs($this->coreUpgrader, [$phpString]);

        $this->assertSame(['jack', [1, 2, 3, 4]], $parametersExtracted);

        $method = self::getMethod('extractParametersFromString');
        $phpString = "('feature_flag', 'stability', 'VARCHAR(64) DEFAULT \'beta\' NOT NULL')";
        $parametersExtracted = $method->invokeArgs($this->coreUpgrader, [$phpString]);

        $this->assertSame(['feature_flag', 'stability', 'VARCHAR(64) DEFAULT \'beta\' NOT NULL'], $parametersExtracted);
    }

    /**
     * @throws ReflectionException
     */
    public function testExtractParametersFromPhpStringParsingExceptions()
    {
        $method = self::getMethod('extractParametersFromString');
        $phpString = '($this->)';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Error while parsing the parameter string.');

        $method->invokeArgs($this->coreUpgrader, [$phpString]);

        $phpString = '(return 1;)';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Error while parsing the parameter string.');

        $method->invokeArgs($this->coreUpgrader, [$phpString]);
    }
}
