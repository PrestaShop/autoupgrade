<?php

namespace unit;

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

    public function testGetEnvValueNotFound()
    {
        $environment = new Environment();
        $this->assertNull($environment->getEnvValue('NON_EXISTING_VAR'));
    }

    /**
     * @dataProvider analyticsOptOutProvider
     */
    public function testHasOptedOutAnalytics($value, $expected)
    {
        $_SERVER[Environment::URL_TRACKING_ENV_NAME] = $value;
        $environment = new Environment();
        $this->assertEquals($expected, $environment->hasOptedOutAnalytics());
    }

    public function analyticsOptOutProvider()
    {
        return [
            'no value' => [null, true],
            'false string' => ['false', true],
            '0 string' => ['0', true],
            'off string' => ['off', true],
            'no string' => ['no', true],
            'empty string' => ['', true],
            'true string' => ['true', false],
            '1 string' => ['1', false],
            'on string' => ['on', false],
            'yes string' => ['yes', false],
        ];
    }
}
