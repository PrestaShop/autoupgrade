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
 * Updates ps_cart_rule_type_lang table to add translations
 *
 * This method will update ps_cart_rule_type_lang with translated wordings for all available languages
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_910_init_cart_rule_type_lang_translations()
{
    $translator = Context::getContext()->getTranslator();
    $domain = 'Admin.Catalog.Feature';

    // define all translations by ids for discount types
    $translations = [
        [
            'id_cart_rule_type' => 1,
            'name' => 'On free shipping',
            'description' => 'Discount that provides free shipping to the order',
        ],
        [
            'id_cart_rule_type' => 2,
            'name' => 'On cart amount',
            'description' => 'Discount applied to cart',
        ],
        [
            'id_cart_rule_type' => 3,
            'name' => 'On total order',
            'description' => 'Discount applied to the order',
        ],
        [
            'id_cart_rule_type' => 4,
            'name' => 'On catalog products',
            'description' => 'Discount applied to specific products',
        ],
        [
            'id_cart_rule_type' => 5,
            'name' => 'On free gift',
            'description' => 'Discount that provides a free gift product',
        ],
    ];

    // get languages
    $languages = Language::getLanguages();

    // for each language, populate cart_rule_type_lang
    foreach ($languages as $lang) {
        foreach ($translations as $trans) {
            $id_cart_rule_type = $trans['id_cart_rule_type'];
            $name = pSQL($translator->trans($trans['name'], [], $domain, $lang['locale']));
            $description = pSQL($translator->trans($trans['description'], [], $domain, $lang['locale']));

            $updateQuery = sprintf(
                'REPLACE INTO `%scart_rule_type_lang` VALUES (%s, %s, "%s", "%s")',
                _DB_PREFIX_,
                $id_cart_rule_type,
                $lang['id_lang'],
                $name,
                $description
            );
            DbWrapper::execute($updateQuery);
        }
    }
}
