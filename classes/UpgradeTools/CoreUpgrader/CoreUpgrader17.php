<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader;

use PrestaShop\Module\AutoUpgrade\Exceptions\ProcessException;

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
     * @throws ProcessException
     * @throws \Exception
     */
    protected function upgradeLanguage($lang): void
    {
        $isoCode = $lang['iso_code'];

        if (!\Validate::isLangIsoCode($isoCode) || !\Language::getLangDetails($isoCode)) {
            $this->logger->debug($this->container->getTranslator()->trans('%lang% is not a valid iso code, skipping', ['%lang%' => $isoCode]));

            return;
        }
        $errorsLanguage = [];

        if (!\Language::downloadLanguagePack($isoCode, _PS_VERSION_, $errorsLanguage)) {
            throw new ProcessException($this->container->getTranslator()->trans('Download of the language pack %lang% failed. %details%', ['%lang%' => $isoCode, '%details%' => implode('; ', $errorsLanguage)]));
        }

        $lang_pack = \Language::getLangDetails($isoCode);
        \Language::installSfLanguagePack($lang_pack['locale'], $errorsLanguage);

        if ($this->container->getUpdateConfiguration()->shouldRegenerateMailTemplates()) {
            \Language::installEmailsLanguagePack($lang_pack, $errorsLanguage);
        }

        if (!empty($errorsLanguage)) {
            throw new ProcessException($this->container->getTranslator()->trans('Error while updating translations for the language pack %lang%. %details%', ['%lang%' => $isoCode, '%details%' => implode('; ', $errorsLanguage)]));
        }
        \Language::loadLanguages();

        // TODO: Update AdminTranslationsController::addNewTabs to install tabs translated

        // CLDR has been updated on PS 1.7.6.0. From this version, updates are not needed anymore.
        if (version_compare($this->container->getUpdateState()->getDestinationVersion(), '1.7.6.0', '<')) {
            $cldrUpdate = new \PrestaShop\PrestaShop\Core\Cldr\Update(_PS_TRANSLATIONS_DIR_);
            $cldrUpdate->fetchLocale(\Language::getLocaleByIso($isoCode));
        }
    }
}
