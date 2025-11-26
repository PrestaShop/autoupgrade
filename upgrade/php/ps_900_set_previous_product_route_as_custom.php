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
