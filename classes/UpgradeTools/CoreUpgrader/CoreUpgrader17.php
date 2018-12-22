<?php

/*
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader;

use PrestaShop\Module\AutoUpgrade\UpgradeException;

/**
 * Class used to modify the core of PrestaShop, on the files are copied on the filesystem.
 * It will run subtasks such as database upgrade, language upgrade etc.
 */
class CoreUpgrader17 extends CoreUpgrader
{
    protected function initConstants()
    {
        parent::initConstants();

        /*if (!file_exists(SETTINGS_FILE_PHP)) {
            throw new UpgradeException($this->container->getTranslator()->trans('The app/config/parameters.php file was not found.', array(), 'Modules.Autoupgrade.Admin'));
        }
        if (!file_exists(SETTINGS_FILE_YML)) {
            throw new UpgradeException($this->container->getTranslator()->trans('The app/config/parameters.yml file was not found.', array(), 'Modules.Autoupgrade.Admin'));
        }*/
    }

    protected function upgradeDb($oldversion)
    {
        parent::upgradeDb($oldversion);

        $commandResult = $this->container->getSymfonyAdapter()->runSchemaUpgradeCommand();
        if (0 !== $commandResult['exitCode']) {
            throw (new UpgradeException($this->container->getTranslator()->trans('Error upgrading Doctrine schema', [], 'Modules.Autoupgrade.Admin')))
                ->setQuickInfos(explode("\n", $commandResult['output']));
        }
    }

    protected function upgradeLanguage($lang)
    {
        $isoCode = $lang['iso_code'];

        if (!\Validate::isLangIsoCode($isoCode)) {
            return;
        }
        $errorsLanguage = [];

        \Language::downloadLanguagePack($isoCode, _PS_VERSION_, $errorsLanguage);

        $lang_pack = \Language::getLangDetails($isoCode);
        \Language::installSfLanguagePack($lang_pack['locale'], $errorsLanguage);

        if (!$this->container->getUpgradeConfiguration()->shouldKeepMails()) {
            \Language::installEmailsLanguagePack($lang_pack, $errorsLanguage);
        }

        if (!empty($errorsLanguage)) {
            throw new UpgradeException($this->container->getTranslator()->trans('Error updating translations', [], 'Modules.Autoupgrade.Admin'));
        }
        \Language::loadLanguages();

        // TODO: Update AdminTranslationsController::addNewTabs to install tabs translated

        $cldrUpdate = new \PrestaShop\PrestaShop\Core\Cldr\Update(_PS_TRANSLATIONS_DIR_);
        $cldrUpdate->fetchLocale(\Language::getLocaleByIso($isoCode));
    }
}
