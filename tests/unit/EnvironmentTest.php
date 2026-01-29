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

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Environment;

class EnvironmentTest extends TestCase
{
    private $originalServer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalServer = $_SERVER;
        // We need to unset the variables we are going to test to avoid side effects
        unset($_SERVER['TEST_VAR']);
        unset($_SERVER['TEST_BOOLEAN_VAR']);
        putenv('TEST_VAR');
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        parent::tearDown();
    }

    public function testGetEnvValueFromServer()
    {
        $_SERVER['TEST_VAR'] = 'server_value';
        $environment = new Environment();
        $this->assertEquals('server_value', $environment->getEnvValue('TEST_VAR'));
    }

    public function testGetEnvValueFromGetenv()
    {
        putenv('TEST_VAR=getenv_value');
        $environment = new Environment();
        $this->assertEquals('getenv_value', $environment->getEnvValue('TEST_VAR'));
    }

    public function testGetEnvValueFromGetenvAndSuperglobal()
    {
        $_SERVER['TEST_VAR'] = 'server_value';
        putenv('TEST_VAR=getenv_value');
        $environment = new Environment();
        $this->assertEquals('server_value', $environment->getEnvValue('TEST_VAR'));
    }

    public function testGetEnvValueNotFound()
    {
        $environment = new Environment();
        $this->assertNull($environment->getEnvValue('NON_EXISTING_VAR'));
    }

    /**
     * @dataProvider booleanFromStringProvider
     */
    public function testGetBooleanFromStringValue(string $value, bool $expected)
    {
        $envVarName = 'TEST_BOOLEAN_VAR';
        $_SERVER[$envVarName] = $value;
        $environment = new Environment();
        // The default value should not affect the result here
        $this->assertSame($expected, $environment->getBoolean($envVarName, false));
        $this->assertSame($expected, $environment->getBoolean($envVarName, true));
    }

    public function booleanFromStringProvider(): array
    {
        return [
            'false string' => ['false', false],
            '0 string' => ['0', false],
            'off string' => ['off', false],
            'no string' => ['no', false],
            'empty string' => ['', false],
            'random string' => ['random_string', false],
            'true string' => ['true', true],
            '1 string' => ['1', true],
            'on string' => ['on', true],
            'yes string' => ['yes', true],
        ];
    }

    /**
     * @dataProvider defaultValueProvider
     */
    public function testGetBooleanDefaultValue(bool $defaultToTest)
    {
        $environment = new Environment();
        $this->assertSame($defaultToTest, $environment->getBoolean('NON_EXISTING_VAR', $defaultToTest));
    }

    public function defaultValueProvider(): array
    {
        return [
            'default is true' => [true],
            'default is false' => [false],
        ];
    }

    public function testGetBooleanReturnsDefaultWhenValueIsNull()
    {
        $envVarName = 'TEST_BOOLEAN_VAR';
        $_SERVER[$envVarName] = null;
        $environment = new Environment();
        $this->assertTrue($environment->getBoolean($envVarName, true));
        $this->assertFalse($environment->getBoolean($envVarName, false));
    }
}
