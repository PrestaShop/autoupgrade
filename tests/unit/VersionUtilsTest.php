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
use PrestaShop\Module\AutoUpgrade\VersionUtils;

class VersionUtilsTest extends TestCase
{
    public function testGetHumanReadableVersionOf()
    {
        $version = VersionUtils::getHumanReadableVersionOf(70103);

        $this->assertSame('7.1.3', $version);
    }

    public function testGetHumanReadableVersionOfFailForBadType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Version must be an integer.');
        VersionUtils::getHumanReadableVersionOf('70103');
    }

    public function testGetHumanReadableVersionOfFailForNegativeValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Version cannot be negative.');
        VersionUtils::getHumanReadableVersionOf(-70103);
    }

    public function testGetHumanReadableVersionOfFailForTooLargeValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Version must be a five-digit integer.');
        VersionUtils::getHumanReadableVersionOf(970103);
    }

    public function testGetHumanReadableVersionOfFailForTooSmallValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Version must be a five-digit integer.');
        VersionUtils::getHumanReadableVersionOf(7103);
    }

    public function testGetPhpVersionId()
    {
        $version = VersionUtils::getPhpVersionId('7.1.0');

        $this->assertSame(70100, $version);

        $version = VersionUtils::getPhpVersionId('7.1');

        $this->assertSame(70100, $version);

        $version = VersionUtils::getPhpVersionId('7.2.18');

        $this->assertSame(70218, $version);

        $version = VersionUtils::getPhpVersionId('7.2.5');

        $this->assertSame(70205, $version);
    }

    public function testGetPhpVersionIdFailForBadType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Version must be a string.');
        VersionUtils::getPhpVersionId(710);
    }

    public function testGetPhpVersionIdFailForEmptyString()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Version string cannot be empty.');
        VersionUtils::getPhpVersionId('');
    }

    public function testGetPhpVersionIdFailForNonNumericValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Version parts must be numeric.');
        VersionUtils::getPhpVersionId('7.a');
    }

    public function testGetPhpMajorMinorVersionId()
    {
        $version = VersionUtils::getPhpMajorMinorVersionId(70218);

        $this->assertSame(70200, $version);
    }

    /**
     * @dataProvider providerOfPrestaShopVersions
     */
    public function testSplitOfPrestaShopVersion(string $inputVersion, array $expected)
    {
        $version = VersionUtils::splitPrestaShopVersion($inputVersion);

        $this->assertEquals($expected, $version);
    }

    public function providerOfPrestaShopVersions()
    {
        return [
            ['1.6.1.12', ['major' => '1.6', 'minor' => '1.6.1', 'patch' => '1.6.1.12']],
            ['1.7.8.11', ['major' => '1.7', 'minor' => '1.7.8', 'patch' => '1.7.8.11']],
            ['8.1.5', ['major' => '8', 'minor' => '8.1', 'patch' => '8.1.5']],
            ['8.1.5', ['major' => '8', 'minor' => '8.1', 'patch' => '8.1.5']],
            ['9.0.0', ['major' => '9', 'minor' => '9.0', 'patch' => '9.0.0']],
            ['10.1.5', ['major' => '10', 'minor' => '10.1', 'patch' => '10.1.5']],
        ];
    }

    public function testGetPrestashopMinorVersionFailForBadType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Version must be a string.');

        VersionUtils::splitPrestaShopVersion(1)['minor'];
    }

    public function testGetPrestashopMinorVersionFailForEmptyString()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Version string cannot be empty.');

        VersionUtils::splitPrestaShopVersion('')['minor'];
    }

    public function testGetPrestashopMinorVersionFailForIncorrectEntryFormatV1()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid version format. Expected format: X.Y.Z or X.Y.Z.W');

        VersionUtils::splitPrestaShopVersion('1')['minor'];
    }

    public function testGetPrestashopMinorVersionFailForIncorrectEntryFormatV2()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid version format. Expected format: X.Y.Z or X.Y.Z.W');

        VersionUtils::splitPrestaShopVersion('1.7.7.10.1')['minor'];
    }
}
