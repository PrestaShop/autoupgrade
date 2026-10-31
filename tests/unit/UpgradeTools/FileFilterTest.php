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
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

/**
 * The release archive ships a .env: a shop that already has one keeps it, a shop coming from 8.x gets the
 * release's, since the 9.x kernel does not boot without the file.
 */
class FileFilterTest extends TestCase
{
    /** @var string */
    private $shopRoot;

    protected function setUp()
    {
        parent::setUp();
        $this->shopRoot = sys_get_temp_dir() . '/fileFilterShop' . uniqid();
        mkdir($this->shopRoot);
    }

    protected function tearDown()
    {
        @unlink($this->shopRoot . '/.env');
        rmdir($this->shopRoot);
        parent::tearDown();
    }

    public function testTheShopEnvIsKeptWhenItHasOne()
    {
        touch($this->shopRoot . '/.env');

        $this->assertContains('/.env', $this->getFilesToIgnoreOnUpgrade());
    }

    public function testTheReleaseEnvIsInstalledWhenTheShopHasNone()
    {
        $ignored = $this->getFilesToIgnoreOnUpgrade();

        $this->assertNotContains('/.env', $ignored);
        $this->assertContains('/app/config/parameters.php', $ignored);
    }

    /**
     * @return string[]
     */
    private function getFilesToIgnoreOnUpgrade()
    {
        $container = new UpgradeContainer($this->shopRoot, $this->shopRoot . '/admin');

        return $container->getFileFilter()->getFilesToIgnoreOnUpgrade();
    }
}
