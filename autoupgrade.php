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
class Autoupgrade extends Module
{
    /**
     * @var int
     */
    public $multishop_context;

    /**
     * @var \PrestaShop\Module\AutoUpgrade\UpgradeContainer
     */
    protected $container;

    public function __construct()
    {
        $this->name = 'autoupgrade';
        $this->tab = 'administration';
        $this->author = 'PrestaShop';
        $this->version = '7.4.3';
        $this->need_instance = 1;
        $this->module_key = '926bc3e16738b7b834f37fc63d59dcf8';

        $this->bootstrap = true;
        parent::__construct();

        $this->multishop_context = Shop::CONTEXT_ALL;

        $this->displayName = $this->trans('Update Assistant');
        $this->description = $this->trans('The Update Assistant module helps you backup, update and restore your PrestaShop store. With just a few clicks, you can move to the latest version of PrestaShop with confidence.');

        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
    }

    /**
     * following the Core documentation :
     * https://devdocs.prestashop-project.org/8/modules/creation/module-translation/new-system/#translating-your-module
     *
     * @return bool
     */
    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function install()
    {
        require_once _PS_ROOT_DIR_ . '/modules/autoupgrade/classes/VersionUtils.php';
        if (!\PrestaShop\Module\AutoUpgrade\VersionUtils::isActualPHPVersionCompatible()) {
            $this->_errors[] = $this->trans(
                'This module requires PHP %s to work properly. Please upgrade your server configuration.',
                [\PrestaShop\Module\AutoUpgrade\VersionUtils::getHumanReadableVersionOf(\PrestaShop\Module\AutoUpgrade\VersionUtils::MODULE_COMPATIBLE_PHP_VERSION)]
            );

            return false;
        }

        // If the "AdminSelfUpgrade" tab does not exist yet, create it
        $moduleTabName = 'AdminSelfUpgrade';
        if (!Tab::getIdFromClassName($moduleTabName)) {
            $tab = new Tab();
            $tab->class_name = $moduleTabName;
            $tab->icon = 'arrow_upward';
            $tab->module = 'autoupgrade';

            // We use DEFAULT to add Upgrade tab as a standalone tab in the back office menu
            $tab->id_parent = (int) Tab::getIdFromClassName('CONFIGURE');

            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = 'Update assistant';
            }
            if (!$tab->save()) {
                return $this->_abortInstall($this->trans('Unable to create the %s tab', [$moduleTabName]));
            }
        }

        $ajaxTabName = 'AdminAutoupgradeAjax';
        if (!Tab::getIdFromClassName($ajaxTabName)) {
            $ajaxTab = new Tab();
            $ajaxTab->class_name = $ajaxTabName;
            $ajaxTab->module = 'autoupgrade';
            $ajaxTab->id_parent = -1;

            foreach (Language::getLanguages(false) as $lang) {
                $ajaxTab->name[(int) $lang['id_lang']] = 'Update assistant';
            }
            if (!$ajaxTab->save()) {
                return $this->_abortInstall($this->trans('Unable to create the %s tab', [$ajaxTabName]));
            }
        }

        return parent::install() && $this->registerHook('displayBackOfficeHeader') && $this->registerHook('displayBackOfficeEmployeeMenu');
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        // Delete the module Back-office tab
        $id_tab = Tab::getIdFromClassName('AdminSelfUpgrade');
        if ($id_tab) {
            $tab = new Tab((int) $id_tab);
            $tab->delete();
        }

        $id_ajax_tab = Tab::getIdFromClassName('AdminAutoupgradeAjax');
        if ($id_ajax_tab) {
            $ajaxTab = new Tab((int) $id_ajax_tab);
            $ajaxTab->delete();
        }

        // Remove the 'autoupgrade' admin directory except backups
        $this->getUpgradeContainer()->getFilesystemAdapter()->clearDirectory(_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'autoupgrade', ['backup']);

        return parent::uninstall();
    }

    /**
     * @return void
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminSelfUpgrade'));
    }

    /**
     * Set installation errors and return false.
     *
     * @param string $error Installation abortion reason
     *
     * @return bool Always false
     */
    protected function _abortInstall($error)
    {
        $this->_errors[] = $error;

        return false;
    }

    /**
     * Adapter for trans calls, existing only on PS 1.7.
     * Making them available for PS 1.6 as well.
     *
     * @param string $id
     * @param array<int|string, int|string> $parameters $parameters
     * @param string $domain
     * @param string $locale
     *
     * @return string
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        require_once _PS_ROOT_DIR_ . '/modules/autoupgrade/classes/UpgradeTools/Translator.php';

        $translator = new \PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator(
            _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'autoupgrade' . DIRECTORY_SEPARATOR . 'translations' . DIRECTORY_SEPARATOR,
            \Context::getContext()->language->iso_code
        );

        return $translator->trans($id, $parameters);
    }

    /**
     * Hook called after the backoffice content is rendered.
     * Used to display the update notification dialog.
     *
     * @return string
     *
     * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException
     * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (!$this->initAutoloaderIfCompliant()) {
            return '';
        }

        if (isset($this->context->controller->ajax) && $this->context->controller->ajax) {
            return '';
        }

        return (new \PrestaShop\Module\AutoUpgrade\Hooks\DisplayBackOfficeHeader($this->getUpgradeContainer()))->renderUpdateNotification();
    }

    /**
     * Only available from PS8.
     *
     * @param array{links: \PrestaShop\PrestaShop\Core\Action\ActionsBarButtonsCollection} $params
     *
     * @return void
     */
    public function hookDisplayBackOfficeEmployeeMenu(array $params)
    {
        if (
            !$this->initAutoloaderIfCompliant()
            || !class_exists(\PrestaShop\PrestaShop\Core\Action\ActionsBarButtonsCollection::class)
            || !class_exists(\PrestaShop\PrestaShop\Core\Action\ActionsBarButton::class)
            // @phpstan-ignore instanceof.alwaysTrue (Depends on the PrestaShop version)
            || !($params['links'] instanceof \PrestaShop\PrestaShop\Core\Action\ActionsBarButtonsCollection)
        ) {
            return;
        }

        $params['links']->add(
            new \PrestaShop\PrestaShop\Core\Action\ActionsBarButton(
                __CLASS__,
                [
                    'link' => \PrestaShop\Module\AutoUpgrade\DocumentationLinks::getPrestashopReleasesUrl(),
                    'icon' => 'history',
                    'isExternalLink' => true,
                ],
                $this->trans('Discover the latest releases')
            )
        );
    }

    /**
     * @return bool
     */
    public function initAutoloaderIfCompliant()
    {
        require_once _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'autoupgrade' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'VersionUtils.php';
        if (!\PrestaShop\Module\AutoUpgrade\VersionUtils::isActualPHPVersionCompatible()) {
            return false;
        }

        $autoloadPath = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }

        return true;
    }

    /**
     * @return \PrestaShop\Module\AutoUpgrade\UpgradeContainer
     */
    public function getUpgradeContainer()
    {
        if (null === $this->container) {
            $this->container = new \PrestaShop\Module\AutoUpgrade\UpgradeContainer(_PS_ROOT_DIR_, realpath(_PS_ADMIN_DIR_));
        }

        return $this->container;
    }
}
