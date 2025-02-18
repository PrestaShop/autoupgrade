<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\AutoUpgrade\Twig;

use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetFunctionExtension3 extends AbstractExtension
{
    /** @var UpgradeContainer */
    private $upgradeContainer;

    public function __construct(
        UpgradeContainer $upgradeContainer
    ) {
        // We set the whole container because some of the variables we need
        // will be set later in the script execution.
        $this->upgradeContainer = $upgradeContainer;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', [$this, 'asset']),
        ];
    }

    /**
     * @param string $asset
     */
    public function asset(string $asset): string
    {
        return $this->upgradeContainer->getAssetsEnvironment()->getAssetsBaseUrl($this->upgradeContainer->getRequest()) . '/' . $asset;
    }
}
