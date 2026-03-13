<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * Updates ps_tab_lang table for a given domain and className
 *
 * This method will fetch the tab from className, and update ps_tab_lang
 * with translated wordings for all available languages
 *
 * @param string $domain
 * @param string $className
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function ps_update_tab_lang($domain, $className)
{
    $translator = Context::getContext()->getTranslator();

    // get tab ID
    $tabQuery = sprintf(
        'SELECT id_tab, wording FROM `%stab` WHERE `class_name` = "%s"',
        _DB_PREFIX_,
        $className
    );
    $tab = DbWrapper::getRow($tabQuery);

    if (empty($tab)) {
        return;
    }

    // get languages
    $languages = Language::getLanguages();

    // for each language, update tab_lang
    foreach ($languages as $lang) {
        $tabName = pSQL(
            $translator->trans(
                $tab['wording'],
                [],
                $domain,
                $lang['locale']
            )
        );

        $updateQuery = sprintf(
            'UPDATE `%stab_lang` SET `name` = "%s" WHERE `id_tab` = "%s" AND `id_lang` = "%s"',
            _DB_PREFIX_,
            $tabName,
            $tab['id_tab'],
            $lang['id_lang']
        );
        DbWrapper::execute($updateQuery);
    }
}
