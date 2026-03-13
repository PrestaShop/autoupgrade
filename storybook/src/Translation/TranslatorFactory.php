<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Translation;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Component\HttpFoundation\RequestStack;

class TranslatorFactory
{
    public static function createTranslator(string $translationsFilesPath, RequestStack $request): Translator
    {
        return new Translator(
            $translationsFilesPath,
            $request->getCurrentRequest() ? $request->getCurrentRequest()->getLocale() : null
        );
    }
}
