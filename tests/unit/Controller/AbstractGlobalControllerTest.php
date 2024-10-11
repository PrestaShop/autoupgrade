<?php

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\HttpFoundation\Request;

class AbstractGlobalControllerTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        require_once __DIR__ . '/DummyController.php';
    }

    protected function setUp()
    {
        parent::setUp();

        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }
    }

    /**
     * @dataProvider redirectionTestsProvider
     */
    public function testRedirectTo($destination, $currentUrl, $expectedUrl)
    {
        $upgradeContainer = $this->getMockBuilder(UpgradeContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $server = [
            'HTTP_HOST' => 'localhost',
            'SERVER_PORT' => '80',
            'QUERY_STRING' => $currentUrl,
            'PHP_SELF' => '/hello-world/admin-wololo',
            'SCRIPT_FILENAME' => '/yo/doge/index.php',
            'REQUEST_URI' => 'hello-world/admin-wololo/index.php',
        ];
        $request = new Request([], [], [], [], [], $server);
        $controller = new DummyController($upgradeContainer, $request);

        $this->assertSame($expectedUrl, $controller->routeThatRedirectsTo($destination)->getTargetUrl());
    }

    public static function redirectionTestsProvider()
    {
        return [
            [Routes::HOME_PAGE, 'route=update-page-update', 'http://localhost/hello-world/admin-wololo/index.php?route=home-page'],
            [Routes::HOME_PAGE, '', 'http://localhost/hello-world/admin-wololo/index.php?route=home-page'],
            [Routes::HOME_PAGE, 'token=oh-no&route=oh-yes', 'http://localhost/hello-world/admin-wololo/index.php?token=oh-no&route=home-page'],
        ];
    }
}
