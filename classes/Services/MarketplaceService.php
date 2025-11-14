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

namespace PrestaShop\Module\AutoUpgrade\Services;

use PrestaShop\Module\AutoUpgrade\Exceptions\MarketplaceApiException;
use PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace\Module;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class MarketplaceService
{
    /** @var Translator */
    private $translator;

    const ADDONS_API_URL = 'https://api.addons.prestashop.com';

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return Module|null
     */
    public function getModuleDetail(string $module)
    {
        $response = @file_get_contents(self::ADDONS_API_URL . '/v2/products/' . $module);

        if (!$response) {
            throw new MarketplaceApiException($this->translator->trans('Error when retrieving data from Distribution API'), MarketplaceApiException::API_NOT_CALLABLE_CODE);
        }

        $data = json_decode($response, true);

        if (!$data || !is_array($data)) {
            return null;
        }

        return Module::fromArray($data);
    }
}
