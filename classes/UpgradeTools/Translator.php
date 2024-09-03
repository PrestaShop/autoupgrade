<?php

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use SimpleXMLElement;

class Translator
{
    const DEFAULT_LANGUAGE = 'en';
    /**
     * @var array<string,string>
     */
    private $translations = [];

    /** @var string */
    private $locale;

    /** @var string */
    private $translationsFilesPath;

    /**
     * @param string $translationsFilesPath
     * @param string $locale
     */
    public function __construct($translationsFilesPath, $locale = self::DEFAULT_LANGUAGE)
    {
        $this->locale = $locale;
        $this->translationsFilesPath = $translationsFilesPath;
    }

    /**
     * Load translations from XLF files.
     *
     * @return void
     *
     * @throws \Exception
     */
    private function loadTranslations()
    {
        // use generic language file (e.g., fr)
        $path = $this->translationsFilesPath . "ModulesAutoupgradeAdmin.{$this->locale}.xlf";
        if (file_exists($path)) {
            $this->loadXlfFile($path);
        }
    }

    /**
     * Load translations from a specific XLF file.
     *
     * @param string $filePath path to the XLF file
     *
     * @return void
     *
     * @throws \Exception
     */
    private function loadXlfFile($filePath)
    {
        $xml = new SimpleXMLElement(file_get_contents($filePath));
        foreach ($xml->file as $file) {
            foreach ($file->body->{'trans-unit'} as $unit) {
                $this->translations[(string) $unit->source] = (string) $unit->target;
            }
        }
    }

    /**
     * Translate a string to the current language.
     *
     * @param string $id
     * @param array<int|string, mixed> $parameters
     * @param string|null $domain
     * @param string|null $locale
     *
     * @return string Translated string with parameters applied
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        if (empty($this->translations)) {
            try {
                $this->loadTranslations();
            } catch (\Exception $e) {
                return $id;
            }
        }
        $translated = isset($this->translations[$id]) ? $this->translations[$id] : $id;

        return $this->applyParameters($translated, $parameters);
    }

    /**
     * @param string $id
     * @param array<int|string, string> $parameters
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

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
