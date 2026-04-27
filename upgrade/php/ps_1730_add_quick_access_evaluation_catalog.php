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
function ps_1730_add_quick_access_evaluation_catalog()
{
    $moduleManagerBuilder = \PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder::getInstance();
    $moduleManager = $moduleManagerBuilder->build();

    $isStatscheckupInstalled = $moduleManager->isInstalled('statscheckup');

    if ($isStatscheckupInstalled) {
        $translator = Context::getContext()->getTranslator();

        DbWrapper::execute('INSERT INTO `' . _DB_PREFIX_ . 'quick_access` SET link = "index.php?controller=AdminStats&module=statscheckup" ');

        $idQuickAccess = (int) DbWrapper::Insert_ID();

        foreach (Language::getLanguages() as $language) {
            DbWrapper::execute('INSERT INTO `' . _DB_PREFIX_ . 'quick_access_lang` SET 
                `id_quick_access` = ' . $idQuickAccess . ',
                `id_lang` = ' . (int) $language['id_lang'] . ',
                `name` = "' . pSQL($translator->trans('Catalog evaluation', [], 'Admin.Navigation.Header')) . '" ');
        }
    }
}
