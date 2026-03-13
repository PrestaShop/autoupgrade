<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Router\Routes;
use PrestaShop\Module\AutoUpgrade\Router\UrlGenerator;
use Symfony\Component\HttpFoundation\Request;

class UrlGeneratorTest extends TestCase
{
    /** @var UrlGenerator */
    private $urlGenerator;

    protected function setUp()
    {
        $adminPath = 'admin-wololo';
        $this->urlGenerator = new UrlGenerator($adminPath);
    }

    public function testGetShopUrlReturnsUrl()
    {
        $server = [
            'HTTP_HOST' => 'localhost',
            'SERVER_PORT' => '80',
            'QUERY_STRING' => '',
            'PHP_SELF' => '/admin-wololo/index.php',
            'SCRIPT_FILENAME' => '/yo/doge/admin-wololo/index.php',
            'REQUEST_URI' => 'index.php',
        ];

        $request = new Request([], [], [], [], [], $server);

        $expectedAbsoluteUrlPathToShop = '/';
        $expectedAbsoluteUrlPathToAdmin = '/admin-wololo';
        $this->assertSame($expectedAbsoluteUrlPathToShop, $this->urlGenerator->getShopAbsolutePathFromRequest($request));
        $this->assertSame($expectedAbsoluteUrlPathToAdmin, $this->urlGenerator->getShopAdminAbsolutePathFromRequest($request));
    }

    public function testUrlWithShopInSubFolder()
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

        $expectedAbsoluteUrlPathToShop = '/hello-world';
        $expectedAbsoluteUrlPathToAdmin = '/hello-world/admin-wololo';
        $this->assertSame($expectedAbsoluteUrlPathToShop, $this->urlGenerator->getShopAbsolutePathFromRequest($request));
        $this->assertSame($expectedAbsoluteUrlPathToAdmin, $this->urlGenerator->getShopAdminAbsolutePathFromRequest($request));
    }

    public function testUrlWithShopInSubFolderBehindSymbolicLink()
    {
        $server = [
            'HTTP_HOST' => 'localhost',
            'SERVER_PORT' => '80',
            'QUERY_STRING' => 'controller=AdminSelfUpgrade&token=0fbeae3341a7e5f5f3fdc901f783271d',
            'PHP_SELF' => '/PrestaShop-zip/admin-wololo/index.php',
            // Script filepath could be different if the root shop is behind a symlink
            'SCRIPT_FILENAME' => '/var/www/html/PrestaShop-zip/admin-wololo/index.php',
            'REQUEST_URI' => '/PrestaShop-zip/admin-wololo/index.php?controller=AdminSelfUpgrade&token=0fbeae3341a7e5f5f3fdc901f783271d',
        ];

        $request = new Request([], [], [], [], [], $server);

        $expectedAbsoluteUrlPathToShop = '/PrestaShop-zip';
        $expectedAbsoluteUrlPathToAdmin = '/PrestaShop-zip/admin-wololo';
        $this->assertSame($expectedAbsoluteUrlPathToShop, $this->urlGenerator->getShopAbsolutePathFromRequest($request));
        $this->assertSame($expectedAbsoluteUrlPathToAdmin, $this->urlGenerator->getShopAdminAbsolutePathFromRequest($request));
    }

    public function testUrlWithCustomEntrypoint()
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

        $expectedAbsoluteUrlPathToShop = '/';
        $expectedAbsoluteUrlPathToAdmin = '/admin-wololo';
        $this->assertSame($expectedAbsoluteUrlPathToShop, $this->urlGenerator->getShopAbsolutePathFromRequest($request));
        $this->assertSame($expectedAbsoluteUrlPathToAdmin, $this->urlGenerator->getShopAdminAbsolutePathFromRequest($request));
    }

    public function testUrlWithShopInSubFolderAndParams()
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

        $expectedAbsoluteUrlPathToShop = '/hello-world';
        $expectedAbsoluteUrlPathToAdmin = '/hello-world/admin-wololo';
        $this->assertSame($expectedAbsoluteUrlPathToShop, $this->urlGenerator->getShopAbsolutePathFromRequest($request));
        $this->assertSame($expectedAbsoluteUrlPathToAdmin, $this->urlGenerator->getShopAdminAbsolutePathFromRequest($request));
    }

    public function testGetUrlToRoute()
    {
        $server = [
            'HTTP_HOST' => 'localhost:8001',
            'SERVER_PORT' => '8001',
            'QUERY_STRING' => '',
            'PHP_SELF' => '/admin-dev/autoupgrade/ajax-upgradetab.php',
            'SCRIPT_FILENAME' => '/var/www/html/admin-dev/autoupgrade/ajax-upgradetab.php',
            'REQUEST_URI' => '/admin-dev/autoupgrade/ajax-upgradetab.php?route=home-page-submit-form',
        ];

        $request = new Request([], [], [], [], [], $server);

        $expected = 'http://localhost:8001/admin-dev/autoupgrade/ajax-upgradetab.php?route=home-page';

        $this->assertSame($expected, $this->urlGenerator->getUrlToRoute($request, Routes::HOME_PAGE));
    }
}
