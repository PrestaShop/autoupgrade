<?php

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use Symfony\Contracts\Translation\TranslatorInterface;
use SimpleXMLElement;

class Translator implements TranslatorInterface
{
    private $translations = [];

    /**
     * Load translations from XLF files.
     */
    private function loadTranslations()
    {
        $language = \Context::getContext()->language->iso_code;

        // Adjust the path to your XLF files as necessary
        $basePath = _PS_MODULE_DIR_ . 'autoupgrade/translations/ModulesAutoupgradeAdmin';

        // use generic language file (e.g., fr)
        $path = $basePath . '.' . $language . '.xlf';
        if (file_exists($path)) {
            $this->loadXlfFile($path);
        }
    }

    /**
     * Load translations from a specific XLF file.
     *
     * @param string $filePath path to the XLF file
     *
     * @throws \Exception
     */
    private function loadXlfFile($filePath)
    {
        $xml = new SimpleXMLElement(file_get_contents($filePath));
        foreach ($xml->file->body->{'trans-unit'} as $unit) {
            $this->translations[(string) $unit->source] = (string) $unit->target;
        }
    }

    /**
     * Translate a string to the current language.
     *
     * @param string $id Original text
     * @param array $parameters Parameters to apply
     * @param string $domain Unused
     * @param string $locale Unused
     *
     * @return string Translated string with parameters applied
     */
    public function trans($id, array $parameters = [], $domain = 'Modules.Autoupgrade.Admin', $locale = null)
    {
        // If PrestaShop core is not instantiated properly, do not try to translate
        if (!method_exists('\Context', 'getContext') || null === \Context::getContext()->language) {
            return $this->applyParameters($id, $parameters);
        }

        if (empty($this->translations)) {
            $this->loadTranslations();
        }
        $translated = isset($this->translations[$id]) ? $this->translations[$id] : $id;

        return $this->applyParameters($translated, $parameters);
    }

    /**
     * @param string $id
     * @param array $parameters
     *
     * @return string Translated string with parameters applied
     *
     * @internal Public for tests
     */
    public function applyParameters($id, array $parameters = [])
    {
        // Replace placeholders for non-numeric keys
        foreach ($parameters as $placeholder => $value) {
            if (is_int($placeholder)) {
                continue;
            }
            $id = str_replace($placeholder, $value, $id);
            unset($parameters[$placeholder]);
        }

        if (!count($parameters)) {
            return $id;
        }

        return call_user_func_array('sprintf', array_merge([$id], $parameters));
    }


    public function getLocale()
    {
        return \Context::getContext()->language->locale;
    }
}
