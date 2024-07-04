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

use Exception;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\PrestaShop\Adapter\Module\Repository\ModuleRepository;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\MailTemplate\Command\GenerateThemeMailTemplatesCommand;
use PrestaShop\PrestaShop\Core\Exception\CoreException;

class CoreUpgrader80 extends CoreUpgrader
{
    protected function initConstants(): void
    {
        $this->forceRemovingFiles();
        parent::initConstants();

        // Container may be needed to run upgrade scripts
        $this->container->getSymfonyAdapter()->initKernel();
    }

    /**
     * Force remove files if they aren't removed properly after files upgrade.
     */
    protected function forceRemovingFiles(): void
    {
        $filesToForceRemove = [
            '/src/PrestaShopBundle/Resources/config/services/adapter/news.yml',
        ];

        foreach ($filesToForceRemove as $file) {
            if (file_exists(_PS_ROOT_DIR_ . $file)) {
                unlink(_PS_ROOT_DIR_ . $file);
            }
        }
    }

    /**
     * @throws UpgradeException
     * @throws Exception
     *
     * @param array<string, mixed> $lang
     */
    protected function upgradeLanguage($lang): void
    {
        $isoCode = $lang['iso_code'];

        if (!\Validate::isLangIsoCode($isoCode)) {
            $this->logger->debug($this->container->getTranslator()->trans('%lang% is not a valid iso code, skipping', ['%lang%' => $isoCode]));

            return;
        }
        $errorsLanguage = [];

        $this->logger->debug($this->container->getTranslator()->trans('Downloading language pack for %lang%', ['%lang%' => $isoCode]));
        if (!\Language::downloadLanguagePack($isoCode, _PS_VERSION_, $errorsLanguage)) {
            throw new UpgradeException($this->container->getTranslator()->trans('Download of the language pack %lang% failed. %details%', ['%lang%' => $isoCode, '%details%' => implode('; ', $errorsLanguage)]));
        }

        $this->logger->debug($this->container->getTranslator()->trans('Installing %lang% language pack', ['%lang%' => $isoCode]));
        $lang_pack = \Language::getLangDetails($isoCode);
        \Language::installSfLanguagePack($lang_pack['locale'], $errorsLanguage);

        if (!$this->container->getUpgradeConfiguration()->shouldKeepMails()) {
            $this->logger->debug($this->container->getTranslator()->trans('Generating mail templates for %lang%', ['%lang%' => $isoCode]));
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
                throw new UpgradeException($this->container->getTranslator()->trans('Cannot generate email templates: %s.', [$e->getMessage()]));
            }
        }

        if (!empty($errorsLanguage)) {
            throw new UpgradeException($this->container->getTranslator()->trans('Error while updating translations for the language pack %lang%. %details%', ['%lang%' => $isoCode, '%details%' => implode('; ', $errorsLanguage)]));
        }
        \Language::loadLanguages();

        // TODO: Update AdminTranslationsController::addNewTabs to install tabs translated
    }

    protected function disableCustomModules(): void
    {
        $moduleRepository = new ModuleRepository(_PS_ROOT_DIR_, _PS_MODULE_DIR_);
        $this->container->getModuleAdapter()->disableNonNativeModules80($this->pathToUpgradeScripts, $moduleRepository);
    }
}
