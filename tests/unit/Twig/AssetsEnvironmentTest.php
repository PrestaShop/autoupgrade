<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Router\UrlGenerator;
use PrestaShop\Module\AutoUpgrade\Twig\AssetsEnvironment;
use Symfony\Component\HttpFoundation\Request;

class AssetsEnvironmentTest extends TestCase
{
    private $assetsEnvironment;

    protected function setUp()
    {
        $adminPath = 'admin-wololo';
        $this->assetsEnvironment = new AssetsEnvironment(new UrlGenerator($adminPath));
    }

    protected function tearDown()
    {
        unset($_ENV['AUTOUPGRADE_DEV_WATCH_MODE']);
    }

    public function testIsDevModeReturnsTrueWhenEnvVarIsSetTo1()
    {
        $_ENV['AUTOUPGRADE_DEV_WATCH_MODE'] = '1';

        $this->assertTrue($this->assetsEnvironment->isDevMode());
    }

    public function testIsDevModeReturnsFalseWhenEnvVarIsNotSet()
    {
        $this->assertFalse($this->assetsEnvironment->isDevMode());
    }

    public function testIsDevModeReturnsFalseWhenEnvVarIsNot1()
    {
        $_ENV['AUTOUPGRADE_DEV_WATCH_MODE'] = '0';

        $this->assertFalse($this->assetsEnvironment->isDevMode());
    }

    public function testGetAssetsBaseUrlReturnsDevUrlInDevMode()
    {
        $_ENV['AUTOUPGRADE_DEV_WATCH_MODE'] = '1';

        $request = new Request();

        $this->assertSame(AssetsEnvironment::DEV_BASE_URL, $this->assetsEnvironment->getAssetsBaseUrl($request));
    }

    public function testGetAssetsBaseUrlReturnsProductionUrl()
    {
        $expectedAbsoluteUrlPathToShop = '/modules/autoupgrade/views';
        $server = [
            'HTTP_HOST' => 'localhost',
            'SERVER_PORT' => '80',
            'QUERY_STRING' => '',
            'PHP_SELF' => '/admin-wololo/index.php',
            'SCRIPT_FILENAME' => '/yo/doge/admin-wololo/index.php',
            'REQUEST_URI' => 'index.php',
        ];

        $request = new Request([], [], [], [], [], $server);

        $this->assertSame($expectedAbsoluteUrlPathToShop, $this->assetsEnvironment->getAssetsBaseUrl($request));
    }

    public function testGetAssetsBaseUrlReturnsProductionUrlWithShopInSubFolder()
    {
        $server = [
            'HTTP_HOST' => 'localhost',
            'SERVER_PORT' => '80',
            'QUERY_STRING' => '',
            'PHP_SELF' => '/hello-world/admin-wololo/index.php',
            'SCRIPT_FILENAME' => '/yo/doge/admin-wololo/index.php',
            'REQUEST_URI' => 'hello-world/index.php',
        ];

        $request = new Request([], [], [], [], [], $server);

        $expectedAbsoluteUrlPathToShop = '/hello-world/modules/autoupgrade/views';
        $this->assertSame($expectedAbsoluteUrlPathToShop, $this->assetsEnvironment->getAssetsBaseUrl($request));
    }

    public function testGetAssetsBaseUrlReturnsProductionUrlWithCustomEntrypoint()
    {
        $server = [
            'HTTP_HOST' => 'localhost',
            'SERVER_PORT' => '80',
            'QUERY_STRING' => '',
            'PHP_SELF' => '/admin-wololo/autoupgrade/ajax-upgradetab.php',
            'SCRIPT_FILENAME' => '/yo/doge/admin-wololo/autoupgrade/ajax-upgradetab.php',
            'REQUEST_URI' => '/admin-wololo/autoupgrade/ajax-upgradetab.php?route=update-step-backup-submit',
        ];

        $request = new Request([], [], [], [], [], $server);

        $expectedAbsoluteUrlPathToShop = '/modules/autoupgrade/views';
        $this->assertSame($expectedAbsoluteUrlPathToShop, $this->assetsEnvironment->getAssetsBaseUrl($request));
    }

    public function testGetAssetsBaseUrlReturnsProductionUrlWithShopInSubFolderAndParams()
    {
        $server = [
            'HTTP_HOST' => 'localhost',
            'SERVER_PORT' => '80',
            'QUERY_STRING' => '',
            'PHP_SELF' => '/hello-world/admin-wololo/index.php',
            'SCRIPT_FILENAME' => '/yo/doge/admin-wololo/index.php',
            'REQUEST_URI' => 'hello-world/admin-wololo/index.php?controller=AdminSelfUpgrade',
        ];

        $request = new Request([], [], [], [], [], $server);

        $expectedAbsoluteUrlPathToShop = '/hello-world/modules/autoupgrade/views';
        $this->assertSame($expectedAbsoluteUrlPathToShop, $this->assetsEnvironment->getAssetsBaseUrl($request));
    }
}
