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
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class ChecksumCompareTest extends TestCase
{
    public function testCompareReleases()
    {
        // Simplest test
        $v1Structure = [
            'composer.lock' => '75111ebd3d058964acc25a0df5f05db4',
            'init.php' => '0c7996ca741c35ec24e82e5b116604dc',
            'phpstan.neon.dist' => '7c19e03d141d22ceb8940ce0e4d71b01',
            'autoload.php' => '6207fe0a4016f9d9947be8e4b8e48b89',
            'app' => [
                'AppKernel.php' => '37d3976fc4877231fa652567e4e02cd4',
                'AppCache.php' => 'a04845405789c16d83832e9cba9b790c',
            ],
            'install' => [
                'index.php' => '0c7996ca741c35ec24e82e5b116604dc',
            ],
        ];
        $v2Structure = [
            'autoload.php' => '6207fe0a4016f9d9947be8e4b8e48b89',
            'init.php' => '0c7996ca741c35ec24e82e5b116604dc',
            'composer.lock' => 'd50e8124b90459a8e92633adcae892ff',
            'app' => [
                'AppCache.php' => 'a04845405789c16d83832e9cba9b790c',
                'AppKernel.php' => '1d6ea5b88cbf8e6b589760991a16094d',
            ],
            'install' => [
                'index.php' => 'c35ec24e741c35ed83832e5b116604dc',
            ],
        ];

        $checksumCompare = (new UpgradeContainer('/html', '/html/admin'))->getChecksumCompare();

        $actual = $checksumCompare->compareReleases($v1Structure, $v2Structure);
        $expected = [
            'deleted' => ['/phpstan.neon.dist'],
            'modified' => [
                '/composer.lock',
                '/app/AppKernel.php',
            ],
        ];
        $this->assertEquals($expected, $actual);
    }
}
