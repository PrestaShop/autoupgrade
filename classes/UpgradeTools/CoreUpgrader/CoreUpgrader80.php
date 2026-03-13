<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader;

use Exception;
use PrestaShop\Module\AutoUpgrade\Exceptions\ProcessException;
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
            if ($this->fileSystem->exists(_PS_ROOT_DIR_ . $file)) {
                $this->fileSystem->remove(_PS_ROOT_DIR_ . $file);
            }
        }
    }

    /**
     * @param array<string, mixed> $lang
     *
     * @throws Exception
     * @throws ProcessException
     */
    protected function upgradeLanguage($lang): void
    {
        $isoCode = $lang['iso_code'];

        if (!\Validate::isLangIsoCode($isoCode) || !\Language::getLangDetails($isoCode)) {
            $this->logger->debug($this->container->getTranslator()->trans('%lang% is not a valid iso code, skipping', ['%lang%' => $isoCode]));

            return;
        }
        $errorsLanguage = [];

        $this->logger->debug($this->container->getTranslator()->trans('Downloading language pack for %lang%', ['%lang%' => $isoCode]));
        if (!\Language::downloadLanguagePack($isoCode, _PS_VERSION_, $errorsLanguage)) {
            throw new ProcessException($this->container->getTranslator()->trans('Download of the language pack %lang% failed. %details%', ['%lang%' => $isoCode, '%details%' => implode('; ', $errorsLanguage)]));
        }

        $this->logger->debug($this->container->getTranslator()->trans('Installing %lang% language pack', ['%lang%' => $isoCode]));
        $lang_pack = \Language::getLangDetails($isoCode);
        \Language::installSfLanguagePack($lang_pack['locale'], $errorsLanguage);

        if ($this->container->getUpdateConfiguration()->shouldRegenerateMailTemplates()) {
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
                throw new ProcessException($this->container->getTranslator()->trans('Cannot generate email templates: %s.', [$e->getMessage()]));
            }
        }

        if (!empty($errorsLanguage)) {
            throw new ProcessException($this->container->getTranslator()->trans('Error while updating translations for the language pack %lang%. %details%', ['%lang%' => $isoCode, '%details%' => implode('; ', $errorsLanguage)]));
        }
        \Language::loadLanguages();

        // TODO: Update AdminTranslationsController::addNewTabs to install tabs translated
    }

    public function disableCustomModules(): void
    {
        $moduleRepository = new ModuleRepository(_PS_ROOT_DIR_, _PS_MODULE_DIR_);
        $this->container->getModuleAdapter()->disableNonNativeModules80($this->pathToUpgradeScripts, $moduleRepository);
    }
}
