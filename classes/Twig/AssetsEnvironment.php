<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Twig;

use PrestaShop\Module\AutoUpgrade\Router\UrlGenerator;
use Symfony\Component\HttpFoundation\Request;

class AssetsEnvironment
{
    const DEV_BASE_URL = 'http://localhost:5173';

    /** @var UrlGenerator */
    protected $urlGenerator;

    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function isDevMode(): bool
    {
        return !empty($_ENV['AUTOUPGRADE_DEV_WATCH_MODE']) && $_ENV['AUTOUPGRADE_DEV_WATCH_MODE'] === '1';
    }

    public function getAssetsBaseUrl(Request $request): string
    {
        if ($this->isDevMode()) {
            return self::DEV_BASE_URL;
        }

        return rtrim($this->urlGenerator->getShopAbsolutePathFromRequest($request), '/') . '/modules/autoupgrade/views';
    }
}
