<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters;

/**
 * Contains the language configuration.
 */
class LanguageConfiguration extends AbstractConfiguration
{
    const ISO_LANGUAGES = 'ISO_LANGUAGES';

    public function getIsoLanguages(): ?string
    {
        return $this->get(self::ISO_LANGUAGES);
    }
}
