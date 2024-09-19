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
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Twig\AssetsEnvironment;
use Symfony\Component\HttpFoundation\Request;

class AssetsEnvironmentTest extends TestCase
{
    private $assetsEnvironment;

    protected function setUp()
    {
        $this->assetsEnvironment = new AssetsEnvironment();
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

    public function testGetAssetsBaseUrlReturnsProductionUrlWhenNotInDevMode()
    {
        $expectedUrl = 'http://localhost/modules/autoupgrade/views';
        $server = [
            'HTTP_HOST' => 'localhost',
            'SERVER_PORT' => '80',
            'QUERY_STRING' => '',
            'PHP_SELF' => '/admin-wololo/index.php',
            'SCRIPT_FILENAME' => '/yo/doge/index.php',
            'REQUEST_URI' => 'index.php',
        ];

        $request = new Request([], [], [], [], [], $server);

        $this->assertSame($expectedUrl, $this->assetsEnvironment->getAssetsBaseUrl($request));
    }

    public function testGetAssetsBaseUrlReturnsProductionUrlWhenNotInDevModeWithSubFolder()
    {
        $server = [
            'HTTP_HOST' => 'localhost',
            'SERVER_PORT' => '80',
            'QUERY_STRING' => '',
            'PHP_SELF' => '/hello-world/admin-wololo/index.php',
            'SCRIPT_FILENAME' => '/yo/doge/index.php',
            'REQUEST_URI' => 'hello-world/index.php',
        ];

        $request = new Request([], [], [], [], [], $server);

        $expectedUrl = 'http://localhost/hello-world/modules/autoupgrade/views';
        $this->assertSame($expectedUrl, $this->assetsEnvironment->getAssetsBaseUrl($request));
    }

    public function testGetAssetsBaseUrlReturnsProductionUrlWhenNotInDevModeWithSubFolderAndParams()
    {
        $server = [
            'HTTP_HOST' => 'localhost',
            'SERVER_PORT' => '80',
            'QUERY_STRING' => '',
            'PHP_SELF' => '/hello-world/admin-wololo/index.php',
            'SCRIPT_FILENAME' => '/yo/doge/index.php',
            'REQUEST_URI' => 'hello-world/admin-wololo/index.php?controller=AdminSelfUpgrade',
        ];

        $request = new Request([], [], [], [], [], $server);

        $expectedUrl = 'http://localhost/hello-world/modules/autoupgrade/views';
        $this->assertSame($expectedUrl, $this->assetsEnvironment->getAssetsBaseUrl($request));
    }
}
