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

namespace unit\Services;

use LogicException;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Models\PrestashopRelease;
use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use PrestaShop\Module\AutoUpgrade\Services\PhpVersionResolverService;
use PrestaShop\Module\AutoUpgrade\VersionUtils;

class PhpVersionResolverServiceTest extends TestCase
{
    /** @var PhpVersionResolverService */
    private $phpVersionResolverService;

    public function setUp()
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }

        $this->distributionApiService = $this->getMockBuilder(DistributionApiService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPhpVersionRequirements', 'getApiEndpoint'])
            ->getMock();

        $this->phpVersionResolverService = new PhpVersionResolverService($this->distributionApiService, '1.7.0.0');
    }

    public function testGetPhpCompatibilityRangeThenDistributionApiThrowException()
    {
        $this->distributionApiService->method('getPhpVersionRequirements')
            ->willThrowException(new DistributionApiException());

        $this->assertEquals(null, $this->phpVersionResolverService->getPhpCompatibilityRange('blabla'));
    }

    public function testGetPhpCompatibilityRange()
    {
        $this->distributionApiService->method('getPhpVersionRequirements')
            ->willReturn([
                'php_min_version' => '7.2.5',
                'php_max_version' => '8.1',
            ]);

        $this->assertEquals([
            'php_min_version' => '7.2.5',
            'php_max_version' => '8.1',
            'php_current_version' => VersionUtils::getHumanReadableVersionOf(PHP_VERSION_ID),
        ], $this->phpVersionResolverService->getPhpCompatibilityRange('8.2.1'));
    }

    public function testInvalidPsVersionCompatibilityRange()
    {
        $this->assertEquals(PhpVersionResolverService::COMPATIBILITY_UNKNOWN, $this->phpVersionResolverService->getPhpRequirementsState(80000, null));
    }

    public function testUnknownCompatibilityRange()
    {
        $this->distributionApiService->method('getPhpVersionRequirements')
            ->willThrowException(new DistributionApiException());

        $this->assertEquals(PhpVersionResolverService::COMPATIBILITY_UNKNOWN, $this->phpVersionResolverService->getPhpRequirementsState(70300, '1.7.7.7'));
    }

    public function testInvalidCompatibilityRange()
    {
        $this->distributionApiService->method('getPhpVersionRequirements')
            ->willReturn([
                'php_min_version' => '7.1.0',
                'php_max_version' => '7.4.0',
            ]);

        $this->assertEquals(PhpVersionResolverService::COMPATIBILITY_INVALID, $this->phpVersionResolverService->getPhpRequirementsState(80000, '1.7.7.7'));
    }

    public function testValidCompatibilityRange()
    {
        $this->distributionApiService->method('getPhpVersionRequirements')
            ->willReturn([
                'php_min_version' => '7.1.0',
                'php_max_version' => '7.4.0',
            ]);

        $this->assertEquals(PhpVersionResolverService::COMPATIBILITY_VALID, $this->phpVersionResolverService->getPhpRequirementsState(70300, '1.7.7.7'));

        $this->distributionApiService->method('getPhpVersionRequirements')
            ->willReturn([
                'php_min_version' => '7.2.5',
                'php_max_version' => '8.1',
            ]);

        $this->assertEquals(PhpVersionResolverService::COMPATIBILITY_VALID, $this->phpVersionResolverService->getPhpRequirementsState(70213, '1.7.7.7'));
    }

    /**
     * @throws UpgradeException
     * @throws DistributionApiException
     * @throws LogicException
     */
    public function testGetPrestashopDestinationReleaseForPHP5()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The minimum version to use the module is PHP 7.1');

        $this->phpVersionResolverService->getPrestashopDestinationRelease(50600);
    }

    /**
     * @throws UpgradeException
     * @throws DistributionApiException
     * @throws LogicException
     */
    public function testGetPrestashopDestinationReleaseWithoutReleases()
    {
        $this->distributionApiService->method('getApiEndpoint')
            ->willReturn(@file_get_contents(__DIR__ . '/../../fixtures/api-distribution/empty_prestashop.json'));

        $this->expectException(UpgradeException::class);
        $this->expectExceptionMessage('Unable to retrieve releases of Prestashop.');

        $this->phpVersionResolverService->getPrestashopDestinationRelease(80000);
    }

    /**
     * @throws UpgradeException
     * @throws DistributionApiException
     * @throws LogicException
     */
    public function testGetPrestashopDestinationReleaseWithoutAutoupgradeCompatibility()
    {
        $this->distributionApiService->method('getApiEndpoint')
            ->will($this->returnValueMap([
                ['/prestashop', @file_get_contents(__DIR__ . '/../../fixtures/api-distribution/prestashop.json')],
                ['/autoupgrade', @file_get_contents(__DIR__ . '/../../fixtures/api-distribution/empty_autoupgrade.json')]
            ]));

        $this->expectException(UpgradeException::class);
        $this->expectExceptionMessage('Unable to retrieve autoupgrade compatibilities.');

        $this->phpVersionResolverService->getPrestashopDestinationRelease(80000);
    }

    /**
     * @return array[]
     */
    public function prestashopDestinationReleaseProvider(): array
    {
        return [
            'too_high_php_version' => [999999, null],
            'max_php_version' => [80100, new PrestashopRelease('8.2.1',
                'stable',
                '8.1',
                '7.2.5',
                'https://api.prestashop-project.org/assets/prestashop/8.2.1/prestashop.zip',
                'https://api.prestashop-project.org/assets/prestashop/8.2.1/prestashop.xml',
                '513bd62a9f9ad35a723f362d88c99790',
                'https://build.prestashop-project.org/news/2025/prestashop-8-2-1-maintenance-release/',
                'open_source'
            )],
            'min_php_version' => [70205, new PrestashopRelease('8.2.1',
                'stable',
                '8.1',
                '7.2.5',
                'https://api.prestashop-project.org/assets/prestashop/8.2.1/prestashop.zip',
                'https://api.prestashop-project.org/assets/prestashop/8.2.1/prestashop.xml',
                '513bd62a9f9ad35a723f362d88c99790',
                'https://build.prestashop-project.org/news/2025/prestashop-8-2-1-maintenance-release/',
                'open_source'
            )],
            'min_php_version_2' => [70103, new PrestashopRelease('1.7.8.11',
                'stable',
                '7.4',
                '7.1.3',
                'https://api.prestashop-project.org/assets/prestashop/1.7.8.11/prestashop.zip',
                'https://api.prestashop-project.org/assets/prestashop/1.7.8.11/prestashop.xml',
                'd29d55f89a2c44bef3d5c51b70e3a771',
                'https://build.prestashop-project.org/news/2024/prestashop-1-7-8-11-maintenance-release/',
                'open_source'
            )],
        ];
    }

    /**
     * @throws UpgradeException
     * @throws DistributionApiException
     * @throws LogicException
     *
     * @dataProvider prestashopDestinationReleaseProvider
     */
    public function testValidGetPrestashopDestinationRelease($input, $expected)
    {
        $this->distributionApiService->method('getApiEndpoint')
            ->will($this->returnValueMap([
                ['/prestashop', @file_get_contents(__DIR__ . '/../../fixtures/api-distribution/prestashop.json')],
                ['/autoupgrade', @file_get_contents(__DIR__ . '/../../fixtures/api-distribution/autoupgrade.json')]
            ]));

        $this->assertEquals($expected, $this->phpVersionResolverService->getPrestashopDestinationRelease($input));
    }
}
