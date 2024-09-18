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

namespace unit\Services;

use LogicException;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Models\PrestashopRelease;
use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use PrestaShop\Module\AutoUpgrade\Services\PhpVersionResolverService;
use PrestaShop\Module\AutoUpgrade\Xml\FileLoader;

class PhpVersionResolverServiceTest extends TestCase
{
    /** @var PhpVersionResolverService */
    private $phpVersionResolverService;

    public function setUp()
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('An issue with this version of PHPUnit and PHP 8+ prevents this test to run.');
        }

        $this->phpVersionResolverService = $this->getMockBuilder(PhpVersionResolverService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPhpCompatibilityRange'])
            ->getMock();
    }

    public function testInvalidCompatibilityRange()
    {
        $this->phpVersionResolverService->method('getPhpCompatibilityRange')
            ->willReturn(['php_min_version' => '7.1.0', 'php_max_version' => '7.4.0']);

        $this->assertEquals(PhpVersionResolverService::COMPATIBILITY_INVALID, $this->phpVersionResolverService->getPhpRequirementsState(80000, '1.7.7.7'));
    }

    public function testValidCompatibilityRange()
    {
        $this->phpVersionResolverService->method('getPhpCompatibilityRange')
            ->willReturn(['php_min_version' => '7.1.0', 'php_max_version' => '7.4.0']);

        $this->assertEquals(PhpVersionResolverService::COMPATIBILITY_VALID, $this->phpVersionResolverService->getPhpRequirementsState(70300, '1.7.7.7'));

        $this->phpVersionResolverService->method('getPhpCompatibilityRange')
            ->willReturn(['php_min_version' => '7.2.5', 'php_max_version' => '8.1']);

        $this->assertEquals(PhpVersionResolverService::COMPATIBILITY_VALID, $this->phpVersionResolverService->getPhpRequirementsState(70213, '1.7.7.7'));
    }

    public function testUnknownCompatibilityRange()
    {
        $this->phpVersionResolverService->method('getPhpCompatibilityRange')
            ->willReturn(null);

        $this->assertEquals(PhpVersionResolverService::COMPATIBILITY_UNKNOWN, $this->phpVersionResolverService->getPhpRequirementsState(70300, '1.7.7.7'));
    }

    /**
     * @throws UpgradeException
     */
    public function testGetLatestPrestashop17ReleaseWillReturnRelease()
    {
        $fileLoader = $this->getMockBuilder(FileLoader::class)
            ->setMethods(['getXmlChannel'])
            ->getMock();

        $channel = simplexml_load_file(__DIR__ . '/../../fixtures/xml/channel.xml');
        $fileLoader->method('getXmlChannel')
            ->willReturn($channel);

        $distributionApiService = $this->getMockBuilder(DistributionApiService::class)
            ->setMethods(['getReleases', 'getPhpVersionRequirements'])
            ->getMock();

        $this->phpVersionResolverService = $this->getMockBuilder(PhpVersionResolverService::class)
            ->setConstructorArgs([$distributionApiService, $fileLoader, '1.6.0.0'])
            ->setMethods(['getPhpCompatibilityRange'])
            ->getMock();

        $prestashopRelease = new PrestashopRelease(
            '1.7.8.11',
            'stable',
            null,
            null,
'https://github.com/PrestaShop/PrestaShop/releases/download/1.7.8.11/prestashop_1.7.8.11.zip',
            'https://api.prestashop.com/xml/md5/1.7.8.11',
            'd29d55f89a2c44bef3d5c51b70e3a771',
            'https://build.prestashop-project.org/news/2024/prestashop-1-7-8-11-maintenance-release/'
        );

        $this->assertEquals($prestashopRelease, $this->phpVersionResolverService->getReleasesFromChannelFile()['1.7']);
    }

    /**
     * @throws UpgradeException
     */
    public function testGetLatestPrestashop17ReleaseWillReturnNull()
    {
        $fileLoader = $this->getMockBuilder(FileLoader::class)
            ->setMethods(['getXmlChannel'])
            ->getMock();

        $distributionApiService = $this->getMockBuilder(DistributionApiService::class)
            ->setMethods(['getReleases', 'getPhpVersionRequirements'])
            ->getMock();

        $this->phpVersionResolverService = $this->getMockBuilder(PhpVersionResolverService::class)
            ->setConstructorArgs([$distributionApiService, $fileLoader, '1.6.0.0'])
            ->setMethods(['getPhpCompatibilityRange'])
            ->getMock();

        $this->expectException(UpgradeException::class);
        $this->expectExceptionMessage('Unable to retrieve channel.xml from API.');

        $this->phpVersionResolverService->getReleasesFromChannelFile()['1.7'];
    }

    /**
     * @throws UpgradeException
     * @throws DistributionApiException
     */
    public function testGetPrestashopDestinationReleaseForPHP71()
    {
        $fileLoader = $this->getMockBuilder(FileLoader::class)
            ->setMethods(['getXmlChannel'])
            ->getMock();

        $channel = simplexml_load_file(__DIR__ . '/../../fixtures/xml/channel.xml');
        $fileLoader->method('getXmlChannel')
            ->willReturn($channel);

        $distributionApiService = $this->getMockBuilder(DistributionApiService::class)
            ->setMethods(['getReleases', 'getPhpVersionRequirements'])
            ->getMock();

        $this->phpVersionResolverService = $this->getMockBuilder(PhpVersionResolverService::class)
            ->setConstructorArgs([$distributionApiService, $fileLoader, '1.6.0.0'])
            ->setMethods(['getPhpCompatibilityRange'])
            ->getMock();

        $prestashopRelease = new PrestashopRelease(
            '1.7.8.11',
            'stable',
            null,
            null,
            'https://github.com/PrestaShop/PrestaShop/releases/download/1.7.8.11/prestashop_1.7.8.11.zip',
            'https://api.prestashop.com/xml/md5/1.7.8.11',
            'd29d55f89a2c44bef3d5c51b70e3a771',
            'https://build.prestashop-project.org/news/2024/prestashop-1-7-8-11-maintenance-release/'
        );

        $this->assertEquals($prestashopRelease, $this->phpVersionResolverService->getPrestashopDestinationRelease(70118));
    }

    /**
     * @throws UpgradeException
     * @throws DistributionApiException
     */
    public function testGetPrestashopDestinationReleaseForPHP73()
    {
        $fileLoader = $this->getMockBuilder(FileLoader::class)
            ->setMethods(['getXmlChannel'])
            ->getMock();

        $channel = simplexml_load_file(__DIR__ . '/../../fixtures/xml/channel.xml');
        $fileLoader->method('getXmlChannel')
            ->willReturn($channel);

        $distributionApiService = $this->getMockBuilder(DistributionApiService::class)
            ->setMethods(['getReleases', 'getPhpVersionRequirements'])
            ->getMock();

        $releases = [
            new PrestashopRelease(
                '8.0.5',
                'stable',
                '8.1',
                '7.2.5'
            ),
            new PrestashopRelease(
                '8.1.7',
                'stable',
                '8.1',
                '7.2.5'
            ),
            new PrestashopRelease(
                '9.0.0',
                'stable',
                '8.1',
                '7.4'
            ),
        ];
        $distributionApiService->method('getReleases')
            ->willReturn($releases);

        $this->phpVersionResolverService = $this->getMockBuilder(PhpVersionResolverService::class)
            ->setConstructorArgs([$distributionApiService, $fileLoader, '1.6.0.0'])
            ->setMethods(['getPhpCompatibilityRange'])
            ->getMock();

        $release = $this->phpVersionResolverService->getPrestashopDestinationRelease(70318);

        $this->assertEquals('8.1.7', $release->getVersion());
    }

    /**
     * @throws UpgradeException
     * @throws DistributionApiException
     */
    public function testGetPrestashopDestinationReleaseWithNoMatchingChannelXml()
    {
        $fileLoader = $this->getMockBuilder(FileLoader::class)
            ->setMethods(['getXmlChannel'])
            ->getMock();

        $channel = simplexml_load_file(__DIR__ . '/../../fixtures/xml/channel.xml');
        $fileLoader->method('getXmlChannel')
            ->willReturn($channel);

        $distributionApiService = $this->getMockBuilder(DistributionApiService::class)
            ->setMethods(['getReleases', 'getPhpVersionRequirements'])
            ->getMock();

        $releases = [
            new PrestashopRelease(
                '8.0.5',
                'stable',
                '8.1',
                '7.2.5'
            ),
            new PrestashopRelease(
                '8.1.7',
                'stable',
                '8.1',
                '7.2.5'
            ),
            new PrestashopRelease(
                '9.0.0',
                'stable',
                '8.1',
                '7.2.5'
            ),
        ];
        $distributionApiService->method('getReleases')
            ->willReturn($releases);

        $this->phpVersionResolverService = $this->getMockBuilder(PhpVersionResolverService::class)
            ->setConstructorArgs([$distributionApiService, $fileLoader, '1.6.0.0'])
            ->setMethods(['getPhpCompatibilityRange'])
            ->getMock();

        $release = $this->phpVersionResolverService->getPrestashopDestinationRelease(70318);

        $this->assertEquals('8.1.7', $release->getVersion());
        $this->assertEquals('https://build.prestashop-project.org/news/2024/prestashop-8-1-7-maintenance-release/', $release->getReleaseNoteUrl());
    }

    /**
     * @throws UpgradeException
     * @throws DistributionApiException
     */
    public function testGetPrestashopDestinationReleaseWithCurrentVersionUpToDate()
    {
        $fileLoader = $this->getMockBuilder(FileLoader::class)
            ->setMethods(['getXmlChannel'])
            ->getMock();

        $channel = simplexml_load_file(__DIR__ . '/../../fixtures/xml/channel.xml');
        $fileLoader->method('getXmlChannel')
            ->willReturn($channel);

        $distributionApiService = $this->getMockBuilder(DistributionApiService::class)
            ->setMethods(['getReleases', 'getPhpVersionRequirements'])
            ->getMock();

        $releases = [
            new PrestashopRelease(
                '8.0.5',
                'stable',
                '8.1',
                '7.2.5'
            ),
            new PrestashopRelease(
                '8.1.7',
                'stable',
                '8.1',
                '7.2.5'
            ),
            new PrestashopRelease(
                '9.0.0',
                'stable',
                '8.1',
                '7.4'
            ),
        ];
        $distributionApiService->method('getReleases')
            ->willReturn($releases);

        $this->phpVersionResolverService = $this->getMockBuilder(PhpVersionResolverService::class)
            ->setConstructorArgs([$distributionApiService, $fileLoader, '8.1.7'])
            ->setMethods(['getPhpCompatibilityRange'])
            ->getMock();

        $release = $this->phpVersionResolverService->getPrestashopDestinationRelease(70318);

        $this->assertEquals(null, $release);
    }

    /**
     * @throws UpgradeException
     * @throws DistributionApiException
     */
    public function testGetPrestashopDestinationReleaseWhenAPIReturnEmptyResponse()
    {
        $fileLoader = $this->getMockBuilder(FileLoader::class)
            ->setMethods(['getXmlChannel'])
            ->getMock();

        $channel = simplexml_load_file(__DIR__ . '/../../fixtures/xml/channel.xml');
        $fileLoader->method('getXmlChannel')
            ->willReturn($channel);

        $distributionApiService = $this->getMockBuilder(DistributionApiService::class)
            ->setMethods(['getReleases', 'getPhpVersionRequirements'])
            ->getMock();

        $releases = [];
        $distributionApiService->method('getReleases')
            ->willReturn($releases);

        $this->phpVersionResolverService = $this->getMockBuilder(PhpVersionResolverService::class)
            ->setConstructorArgs([$distributionApiService, $fileLoader, '1.6.0.0'])
            ->setMethods(['getPhpCompatibilityRange'])
            ->getMock();

        $release = $this->phpVersionResolverService->getPrestashopDestinationRelease(70318);

        $this->assertEquals(null, $release);
    }

    /**
     * @throws UpgradeException
     * @throws DistributionApiException
     */
    public function testGetPrestashopDestinationReleaseForPHP5()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The minimum version to use the module is PHP 7.1');

        $this->phpVersionResolverService->getPrestashopDestinationRelease(50600);
    }
}
