<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
