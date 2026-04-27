<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

/**
 * Test for backward compatibility translation feature.
 */
class TranslatorTest extends TestCase
{
    public function testTranslationInFrench()
    {
        $translator = new Translator(
            __DIR__ . '/../../../fixtures/',
            'fr'
        );

        $source = 'Action %s skipped';
        $parameters = ['Wololo'];

        $expected = 'L\'action Wololo a été ignorée';

        $this->assertSame(
            $expected,
            $translator->trans($source, $parameters)
        );
    }

    /**
     * @dataProvider translationsTestCaseProvider
     */
    public function testTranslationWithoutParams($origin, $parameters, $expected)
    {
        $translator = new Translator(
            __DIR__ . '/../../../../translations/',
            'en'
        );
        $this->assertSame($expected, $translator->applyParameters($origin, $parameters));
    }

    public function translationsTestCaseProvider()
    {
        return [
            // Test with %s in translated text
            [
                'Downloaded archive will come from %s',
                ['https://download.prestashop.com/download/releases/prestashop_1.7.3.0.zip'],
                'Downloaded archive will come from https://download.prestashop.com/download/releases/prestashop_1.7.3.0.zip',
            ],
            // Text without parameter
            [
                'Using class ZipArchive...',
                [],
                'Using class ZipArchive...',
            ],
            // Text with placeholders
            [
                '[TRANSLATION] The translation files have not been merged into file %filename%. Switch to copy %filename%.',
                ['%filename%' => 'doge.txt'],
                '[TRANSLATION] The translation files have not been merged into file doge.txt. Switch to copy doge.txt.',
            ],
        ];
    }
}
