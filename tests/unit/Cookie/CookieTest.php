<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Cookie;

class CookieTest extends TestCase
{
    const MY_TEST_KEY = 'wololo';

    private $cookie;

    protected function setUp()
    {
        parent::setUp();
        $this->cookie = new Cookie('admin', sys_get_temp_dir());
        $this->assertTrue($this->cookie->storeKey(self::MY_TEST_KEY));
    }

    public function testKeyIsGenerated()
    {
        $this->assertSame(self::MY_TEST_KEY, $this->cookie->readKey());
    }

    public function testPermissionGranted()
    {
        $fakeCookie = [
            'id_employee' => 2,
            'autoupgrade' => md5(md5(self::MY_TEST_KEY) . md5(2)),
        ];
        $this->assertTrue($this->cookie->check($fakeCookie));
    }

    public function testPermissionRefused()
    {
        $fakeCookie = [
            'id_employee' => 2,
            'autoupgrade' => 'IHaveNoIdeaWhatImDoing',
        ];
        $this->assertFalse($this->cookie->check($fakeCookie));
    }
}
