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
