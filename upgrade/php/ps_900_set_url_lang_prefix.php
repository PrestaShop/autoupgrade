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
function ps_900_set_url_lang_prefix()
{
    $numberOfActiveLanguages = (int) DbWrapper::getValue(
        'SELECT COUNT(*) AS lang_count FROM `' . _DB_PREFIX_ . 'lang` WHERE `active` = 1'
    );

    if ($numberOfActiveLanguages > 1) {
        Configuration::updateValue('PS_DEFAULT_LANGUAGE_URL_PREFIX', 1);
    } else {
        Configuration::updateValue('PS_DEFAULT_LANGUAGE_URL_PREFIX', 0);
    }
}
