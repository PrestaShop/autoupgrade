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
