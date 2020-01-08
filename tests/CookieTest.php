<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
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
