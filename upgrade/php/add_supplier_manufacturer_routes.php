<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * In Prestashop 1.7.5 the supplier_rule and manufacturer_rule have been modified:
 *      {id}__{rewrite} => supplier/{id}-{rewrite}
 *      {id}_{rewrite}  => brand/{id}-{rewrite}
 *
 * If the merchant kept the original routes the former urls won't be reachable any
 * more and SEO will be lost. So we force a custom rule matching the former format.
 *
 * If the route was customized, no need to do anything. We don't change anything for
 * multi shop either since it will be used it the merchant has already changed them.
 */
function add_supplier_manufacturer_routes()
{
    Configuration::loadConfiguration();
    $legacyRoutes = [
        'supplier_rule' => '{id}__{rewrite}',
        'manufacturer_rule' => '{id}_{rewrite}',
    ];
    foreach ($legacyRoutes as $routeId => $rule) {
        if (!Configuration::get('PS_ROUTE_' . $routeId, null, 0, 0)) {
            Configuration::updateGlobalValue('PS_ROUTE_' . $routeId, $rule);
        }
    }
}
