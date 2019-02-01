<?php
/*
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

/**
 * Test for backward compatibility translation feature.
 */
class TranslatorTest extends TestCase
{
    protected $translator;

    protected function setUp()
    {
        parent::setUp();
        $this->translator = new Translator(__CLASS__);
    }

    /**
     * @dataProvider translationsTestCaseProvider
     */
    public function testTranslationWithoutParams($origin, $parameters, $expected)
    {
        $this->assertSame($expected, $this->translator->applyParameters($origin, $parameters));
    }

    public function translationsTestCaseProvider()
    {
        return array(
            // Test with %s in translated text
            array(
                'Downloaded archive will come from %s',
                array('https://download.prestashop.com/download/releases/prestashop_1.7.3.0.zip'),
                'Downloaded archive will come from https://download.prestashop.com/download/releases/prestashop_1.7.3.0.zip',
            ),
            // Text without parameter
            array(
                'Using class ZipArchive...',
                array(),
                'Using class ZipArchive...',
            ),
            // Text with placeholders
            array(
                '[TRANSLATION] The translation files have not been merged into file %filename%. Switch to copy %filename%.',
                array('%filename%' => 'doge.txt'),
                '[TRANSLATION] The translation files have not been merged into file doge.txt. Switch to copy doge.txt.',
            ),
        );
    }
}
