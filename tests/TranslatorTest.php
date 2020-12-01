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
