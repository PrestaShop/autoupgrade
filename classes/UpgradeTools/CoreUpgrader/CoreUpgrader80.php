<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
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
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader;

use PrestaShop\Module\AutoUpgrade\UpgradeException;
use PrestaShop\PrestaShop\Adapter\Module\Repository\ModuleRepository;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\MailTemplate\Command\GenerateThemeMailTemplatesCommand;
use PrestaShop\PrestaShop\Core\Exception\CoreException;

class CoreUpgrader80 extends CoreUpgrader
{
    protected function initConstants()
    {
        parent::initConstants();

        // Container may be needed to run upgrade scripts
        $this->container->getSymfonyAdapter()->initAppKernel();
    }

    protected function upgradeDb($oldversion)
    {
        parent::upgradeDb($oldversion);

        $commandResult = $this->container->getSymfonyAdapter()->runSchemaUpgradeCommand();
        if (0 !== $commandResult['exitCode']) {
            throw (new UpgradeException($this->container->getTranslator()->trans('Error upgrading Doctrine schema', [], 'Modules.Autoupgrade.Admin')))->setQuickInfos(explode("\n", $commandResult['output']));
        }
    }

    protected function upgradeLanguage($lang)
    {
        $isoCode = $lang['iso_code'];

        if (!\Validate::isLangIsoCode($isoCode)) {
            return;
        }
        $errorsLanguage = [];

        if (!\Language::downloadLanguagePack($isoCode, _PS_VERSION_, $errorsLanguage)) {
            throw new UpgradeException($this->container->getTranslator()->trans('Download of the language pack %lang% failed. %details%', ['%lang%' => $isoCode, '%details%' => implode('; ', $errorsLanguage)], 'Modules.Autoupgrade.Admin'));
        }

        $lang_pack = \Language::getLangDetails($isoCode);
        \Language::installSfLanguagePack($lang_pack['locale'], $errorsLanguage);

        if (!$this->container->getUpgradeConfiguration()->shouldKeepMails()) {
            $mailTheme = \Configuration::get('PS_MAIL_THEME', null, null, null, 'modern');

            $frontTheme = _THEME_NAME_;
            $frontThemeMailsFolder = _PS_ALL_THEMES_DIR_ . $frontTheme . '/mails';
            $frontThemeModulesFolder = _PS_ALL_THEMES_DIR_ . $frontTheme . '/modules';

            $generateCommand = new GenerateThemeMailTemplatesCommand(
                $mailTheme,
                $lang_pack['locale'],
                true,
                is_dir($frontThemeMailsFolder) ? $frontThemeMailsFolder : '',
                is_dir($frontThemeModulesFolder) ? $frontThemeModulesFolder : ''
            );
            /** @var CommandBusInterface $commandBus */
            $commandBus = $this->container->getModuleAdapter()->getCommandBus();

            try {
                $commandBus->handle($generateCommand);
            } catch (CoreException $e) {
                throw new UpgradeException($this->container->getTranslator()->trans('Cannot generate email templates: %s.', [$e->getMessage()], 'Modules.Autoupgrade.Admin'));
            }
        }

        if (!empty($errorsLanguage)) {
            throw new UpgradeException($this->container->getTranslator()->trans('Error while updating translations for lang %lang%. %details%', ['%lang%' => $isoCode, '%details%' => implode('; ', $errorsLanguage)], 'Modules.Autoupgrade.Admin'));
        }
        \Language::loadLanguages();

        // TODO: Update AdminTranslationsController::addNewTabs to install tabs translated
    }

    protected function disableCustomModules()
    {
        $moduleRepository = new ModuleRepository(_PS_ROOT_DIR_, _PS_MODULE_DIR_);
        $this->container->getModuleAdapter()->disableNonNativeModules80($this->pathToUpgradeScripts, $moduleRepository);
    }
}
