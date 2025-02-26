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

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * @return void
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_810_update_product_page_feature_flags()
{
    $featureFlags = DbWrapper::executeS('SELECT name, state FROM `' . _DB_PREFIX_ . 'feature_flag`');

    // Check if one of the feature flag is already enabled
    $productState = 0;
    if (!empty($featureFlags)) {
        foreach ($featureFlags as $featureFlag) {
            if ($featureFlag['name'] === 'product_page_v2' && (int) $featureFlag['state'] === 1) {
                $productState = 1;
                break;
            }
            if ($featureFlag['name'] === 'product_page_v2_multi_shop' && (int) $featureFlag['state'] === 1) {
                $productState = 1;
                break;
            }
        }
    }

    // Update product feature flag with stability, and appropriate state
    DbWrapper::update('feature_flag', [
        'stability' => 'stable',
        'state' => $productState,
        'label_wording' => 'New product page',
    ], '`name` = \'product_page_v2\'');

    // Delete the multishop feature flag
    DbWrapper::delete('feature_flag', '`name` = \'product_page_v2_multi_shop\'');
}
