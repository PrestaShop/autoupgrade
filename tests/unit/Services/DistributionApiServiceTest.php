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
use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Log\WebLogger;
use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use PrestaShop\Module\AutoUpgrade\Services\DownloadService;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Component\Filesystem\Exception\IOException;

class DistributionApiServiceTest extends TestCase
{
    const VALID_JSON = 'https://github.com/PrestaShop/autoupgrade/raw/refs/heads/dev/composer.json';
    const INVALID_JSON = 'https://github.com/PrestaShop/autoupgrade/raw/refs/heads/dev/README.md';

    private $distributionApiService;

    public function setUp()
    {
        $this->distributionApiService = new DistributionApiService(
            new Translator(''),
            new DownloadService(new Translator(''), new WebLogger())
        );
    }

    public function testApiEndpointViaCurlFailed()
    {
        if (ini_get('allow_url_fopen')) {
            $this->markTestSkipped(__METHOD__ . ': skipped, allow_url_fopen is enabled');
        }
        $this->expectException(IOException::class);
        $this->distributionApiService->getApiEndpoint('https://example.com/file.zip');
    }

    public function testApiEndpointViaCurlPassed()
    {
        if (ini_get('allow_url_fopen')) {
            $this->markTestSkipped(__METHOD__ . ': skipped, allow_url_fopen is enabled');
        }
        $this->expectException(DistributionApiException::class);
        $this->distributionApiService->getApiEndpoint(self::VALID_JSON);
    }

    public function testApiEndpointViaFopenFailed()
    {
        if (!ini_get('allow_url_fopen')) {
            $this->markTestSkipped(__METHOD__ . ': skipped, allow_url_fopen is disabled');
        }
        $this->expectException(DistributionApiException::class);
        $this->distributionApiService->getApiEndpoint('https://example.com/file.zip');
    }

    public function testApiEndpointJsonBad()
    {
        if (!ini_get('allow_url_fopen')) {
            $this->markTestSkipped(__METHOD__ . ': skipped, allow_url_fopen is disabled');
        }
        $this->expectException(DistributionApiException::class);
        $this->distributionApiService->getApiEndpoint(self::INVALID_JSON);
    }

    public function testApiEndpointViaFopenPassed()
    {
        if (!ini_get('allow_url_fopen')) {
            $this->markTestSkipped(__METHOD__ . ': skipped, allow_url_fopen is disabled');
        }
        $result = $this->distributionApiService->getApiEndpoint(self::VALID_JSON);
        $this->assertTrue(is_array($result));
    }
}
