<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader;

use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;

/**
 * Class used to modify the core of PrestaShop, on the files are copied on the filesystem.
 * It will run subtasks such as database upgrade, language upgrade etc.
 */
class CoreUpgrader17 extends CoreUpgrader
{
    protected function initConstants(): void
    {
        parent::initConstants();

        // Container may be needed to run upgrade scripts
        $this->container->getSymfonyAdapter()->initKernel();
    }

    /**
     * @throws UpgradeException
     */
    protected function upgradeLanguage($lang): void
    {
        $isoCode = $lang['iso_code'];

        if (!\Validate::isLangIsoCode($isoCode)) {
            return;
        }
        $errorsLanguage = [];

        if (!\Language::downloadLanguagePack($isoCode, _PS_VERSION_, $errorsLanguage)) {
            throw new UpgradeException($this->container->getTranslator()->trans('Download of the language pack %lang% failed. %details%', ['%lang%' => $isoCode, '%details%' => implode('; ', $errorsLanguage)]));
        }

        $lang_pack = \Language::getLangDetails($isoCode);
        \Language::installSfLanguagePack($lang_pack['locale'], $errorsLanguage);

        if (!$this->container->getUpgradeConfiguration()->shouldKeepMails()) {
            \Language::installEmailsLanguagePack($lang_pack, $errorsLanguage);
        }

        if (!empty($errorsLanguage)) {
            throw new UpgradeException($this->container->getTranslator()->trans('Error while updating translations for the language pack %lang%. %details%', ['%lang%' => $isoCode, '%details%' => implode('; ', $errorsLanguage)]));
        }
        \Language::loadLanguages();

        // TODO: Update AdminTranslationsController::addNewTabs to install tabs translated

        // CLDR has been updated on PS 1.7.6.0. From this version, updates are not needed anymore.
        if (version_compare($this->container->getState()->getInstallVersion(), '1.7.6.0', '<')) {
            $cldrUpdate = new \PrestaShop\PrestaShop\Core\Cldr\Update(_PS_TRANSLATIONS_DIR_);
            $cldrUpdate->fetchLocale(\Language::getLocaleByIso($isoCode));
        }
    }
}
