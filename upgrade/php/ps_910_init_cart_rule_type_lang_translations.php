<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
