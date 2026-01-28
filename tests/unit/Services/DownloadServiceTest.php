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

namespace Services;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Log\WebLogger;
use PrestaShop\Module\AutoUpgrade\Services\DownloadService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Component\Filesystem\Exception\IOException;

class DownloadServiceTest extends TestCase
{
    const VALID_URL = 'https://prestashop.com/';
    const INVALID_URL = 'https://example.com/file.zip';
    private $downloadService;

    public function setUp()
    {
        $this->downloadService = new DownloadService(new Translator(''), new WebLogger());
    }

    public function testFetchViaCurlFailed()
    {
        if (ini_get('allow_url_fopen')) {
            $this->markTestSkipped(__METHOD__ . ': skipped, allow_url_fopen is enabled');
        }
        $this->expectException(IOException::class);
        $this->downloadService->fetch(self::INVALID_URL);
    }

    public function testFetchViaCurlPassed()
    {
        if (ini_get('allow_url_fopen')) {
            $this->markTestSkipped(__METHOD__ . ': skipped, allow_url_fopen is enabled');
        }
        $this->expectException(IOException::class);
        $this->downloadService->fetch(self::VALID_URL);
    }

    public function testFetchViaFopenFailed()
    {
        if (!ini_get('allow_url_fopen')) {
            $this->markTestSkipped(__METHOD__ . ': skipped, allow_url_fopen is disabled');
        }
        $this->expectException(IOException::class);
        $this->downloadService->fetch(self::INVALID_URL);
    }

    public function testFetchViaFopenPassed()
    {
        if (!ini_get('allow_url_fopen')) {
            $this->markTestSkipped(__METHOD__ . ': skipped, allow_url_fopen is disabled');
        }
        $result = $this->downloadService->fetch(self::VALID_URL);
        $this->assertNotEmpty($result);
    }

}
