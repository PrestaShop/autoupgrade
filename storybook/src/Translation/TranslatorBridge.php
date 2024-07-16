<?php

namespace App\Translation;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorBridge implements TranslatorInterface
{
    /** @var Translator */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }
}
