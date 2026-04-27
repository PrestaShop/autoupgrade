<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Twig;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Twig_Extension;
use Twig_SimpleFilter;

/**
 * Filter (Support for Twig 1)
 */
class TransFilterExtension extends Twig_Extension
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters(): array
    {
        return [
            new Twig_SimpleFilter('trans', [$this, 'trans']),
        ];
    }

    /**
     * @param array<int|string, int|string> $params
     */
    public function trans(string $string, array $params = []): string
    {
        return $this->translator->trans($string, $params);
    }
}
