<?php

namespace PrestaShop\Module\AutoUpgrade\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Environment;

class EnvironmentTest extends TestCase
{
    private $originalServer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalServer = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        parent::tearDown();
    }

    public function testGetEnvValuePrefersServerOverGetenv()
    {
        $_SERVER['TEST_VAR'] = 'server_value';
        putenv('TEST_VAR=env_value');

        $env = new Environment();
        $this->assertSame('server_value', $env->getEnvValue('TEST_VAR'));
    }

    public function testGetEnvValueFallsBackToGetenv()
    {
        if (isset($_SERVER['TEST_VAR'])) {
            unset($_SERVER['TEST_VAR']);
        }
        putenv('TEST_VAR=env_value');

        $env = new Environment();
        $this->assertSame('env_value', $env->getEnvValue('TEST_VAR'));
    }

    public function testGetEnvValueReturnsNullIfMissing()
    {
        if (isset($_SERVER['TEST_VAR'])) {
            unset($_SERVER['TEST_VAR']);
        }
        putenv('TEST_VAR'); // Remove from env

        $env = new Environment();
        $this->assertNull($env->getEnvValue('TEST_VAR'));
    }

    /**
     * @dataProvider validBooleanProvider
     */
    public function testGetBooleanWithValidValues($value, bool $expected)
    {
        $_SERVER['TEST_BOOL'] = $value;
        $env = new Environment();

        // Should return the expected boolean regardless of default
        $this->assertSame($expected, $env->getBoolean('TEST_BOOL', false));
        $this->assertSame($expected, $env->getBoolean('TEST_BOOL', true));
    }

    public function validBooleanProvider()
    {
        return [
            ['true', true],
            ['1', true],
            ['on', true],
            ['yes', true],
            ['TRUE', true],
            ['Yes', true],
            ['false', false],
            ['0', false],
            ['off', false],
            ['no', false],
            ['FALSE', false],
            ['No', false],
            ['', false], // Empty string evaluates to false
        ];
    }

    /**
     * @dataProvider invalidBooleanProvider
     */
    public function testGetBooleanWithInvalidValuesReturnsDefault($invalidValue)
    {
        $_SERVER['TEST_BOOL'] = $invalidValue;
        $env = new Environment();

        // Invalid values should return the default value because they return NULL with FILTER_NULL_ON_FAILURE
        $this->assertTrue($env->getBoolean('TEST_BOOL', true), "Failed asserting that invalid value '$invalidValue' returns default true");
        $this->assertFalse($env->getBoolean('TEST_BOOL', false), "Failed asserting that invalid value '$invalidValue' returns default false");
    }

    public function invalidBooleanProvider()
    {
        return [
            ['potatoes'],
            ['not_a_bool'],
            ['random'],
            ['2'],
            ['-1'],
        ];
    }

    public function testGetBooleanReturnsDefaultWhenMissing()
    {
        if (isset($_SERVER['TEST_BOOL'])) {
            unset($_SERVER['TEST_BOOL']);
        }
        putenv('TEST_BOOL');

        $env = new Environment();
        // If the variable is missing, getEnvValue returns null.
        // filter_var(null, ..., FILTER_NULL_ON_FAILURE) returns null.
        // So it should return the default value.
        // Wait, confirming filter_var(null) behavior...
        // If filter_var(null) returns false (as "" converts to false), this test might fail for TRUE case.
        // But with FILTER_NULL_ON_FAILURE, it should ideally be NULL.
        // Let's test checking if defaults apply.

        $this->assertTrue($env->getBoolean('TEST_BOOL', true));
        $this->assertFalse($env->getBoolean('TEST_BOOL', false));
    }
}
