<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * In Prestashop 9.0.0 the default product routes have been modified by removing
 * category and EAN from the URL.
 *
 * If the merchant kept the original routes the former urls won't be reachable any
 * more and SEO will be lost. So we force a custom rule matching the former format.
 *
 * If the route was customized, no need to do anything. We don't change anything for
 * multi shop either since it will be used it the merchant has already changed them.
 */
function ps_900_set_previous_product_route_as_custom()
{
    if (!Configuration::get('PS_ROUTE_product_rule', null, 0, 0)) {
        Configuration::updateGlobalValue('PS_ROUTE_product_rule', '{category:/}{id}{-:id_product_attribute}-{rewrite}{-:ean13}.html');
    }
}
