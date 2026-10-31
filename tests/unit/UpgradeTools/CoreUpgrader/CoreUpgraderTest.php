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
     * Every `/* PHP: *\/` directive shipped in the upgrade scripts has to name a function that
     * exists, because a missing one is only a warning at update time - the migration carries on
     * as if the step had run.
     *
     * @throws ReflectionException
     */
    public function testEveryPhpDirectiveInTheUpgradeScriptsResolvesToAFile()
    {
        $extractPhpString = self::getMethod('extractPhpStringFromQuery');
        $extractParameters = self::getMethod('extractParametersAsString');

        $directories = __DIR__ . '/../../../..';
        $files = glob($directories . '/upgrade/sql/*.sql');
        $this->assertNotEmpty($files);

        $checked = 0;
        foreach ($files as $file) {
            // Same splitting as CoreUpgrader::applySqlParams(), so a directive is read exactly as
            // the update reads it rather than as a hand-written string.
            $queries = array_filter(preg_split("/;\s*[\r\n]+/", file_get_contents($file) . "\n"));

            foreach ($queries as $query) {
                $query = trim($query);
                if (strpos($query, '/* PHP:') === false) {
                    continue;
                }

                $phpString = $extractPhpString->invokeArgs($this->coreUpgrader, [$query]);
                if (strpos($phpString, '::') !== false) {
                    continue; // an object method call, which the update refuses on purpose
                }

                $stringParameters = $extractParameters->invokeArgs($this->coreUpgrader, [$phpString]);
                $functionName = str_replace($stringParameters, '', explode('::', $phpString)[0]);

                $this->assertFileExists(
                    $directories . '/upgrade/php/' . strtolower($functionName) . '.php',
                    sprintf('%s calls %s, which has no file', basename($file), var_export($functionName, true))
                );
                ++$checked;
            }
        }

        $this->assertGreaterThan(0, $checked);
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
