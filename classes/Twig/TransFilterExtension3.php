<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Twig;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Filter (Support for Twig 3)
 */
class TransFilterExtension3 extends AbstractExtension
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('trans', [$this, 'trans']),
        ];
    }

    /**
     * @param array<int|string, string> $params
     */
    public function trans(string $string, $params = []): string
    {
        return $this->translator->trans($string, $params);
    }
}
