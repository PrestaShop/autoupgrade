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
class Autoupgrade extends Module
{
    /**
     * @var int
     */
    public $multishop_context;

    public function __construct()
    {
        $this->name = 'autoupgrade';
        $this->tab = 'administration';
        $this->author = 'PrestaShop';
        $this->version = '6.3.0';
        $this->need_instance = 1;
        $this->module_key = '926bc3e16738b7b834f37fc63d59dcf8';

        $this->bootstrap = true;
        parent::__construct();

        $this->multishop_context = Shop::CONTEXT_ALL;

        if (!defined('_PS_ADMIN_DIR_')) {
            if (defined('PS_ADMIN_DIR')) {
                define('_PS_ADMIN_DIR_', PS_ADMIN_DIR);
            } else {
                $this->_errors[] = $this->trans('This version of PrestaShop cannot be upgraded: the PS_ADMIN_DIR constant is missing.');
            }
        }

        $this->displayName = $this->trans('1-Click Upgrade');
        $this->description = $this->trans('Upgrade to the latest version of PrestaShop in a few clicks, thanks to this automated method.');

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
        if (!Tab::getIdFromClassName('AdminSelfUpgrade')) {
            $tab = new Tab();
            $tab->class_name = 'AdminSelfUpgrade';
            $tab->icon = 'upgrade';
            $tab->module = 'autoupgrade';

            // We use DEFAULT to add Upgrade tab as a standalone tab in the back office menu
            $tab->id_parent = (int) Tab::getIdFromClassName('CONFIGURE');

            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = '1-Click Upgrade';
            }
            if (!$tab->save()) {
                return $this->_abortInstall($this->trans('Unable to create the "AdminSelfUpgrade" tab'));
            }
        }

        return parent::install();
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        // Delete the 1-click upgrade Back-office tab
        $id_tab = Tab::getIdFromClassName('AdminSelfUpgrade');
        if ($id_tab) {
            $tab = new Tab((int) $id_tab);
            $tab->delete();
        }

        // Remove the 1-click upgrade working directory
        self::_removeDirectory(_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'autoupgrade');

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
     * @param string $dir
     *
     * @return void
     */
    private static function _removeDirectory($dir)
    {
        if ($handle = @opendir($dir)) {
            while (false !== ($entry = @readdir($handle))) {
                if ($entry != '.' && $entry != '..') {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $entry) === true) {
                        self::_removeDirectory($dir . DIRECTORY_SEPARATOR . $entry);
                    } else {
                        @unlink($dir . DIRECTORY_SEPARATOR . $entry);
                    }
                }
            }

            @closedir($handle);
            @rmdir($dir);
        }
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
            _PS_ROOT_DIR_ . 'modules' . DIRECTORY_SEPARATOR . 'autoupgrade' . DIRECTORY_SEPARATOR . 'translations' . DIRECTORY_SEPARATOR,
            \Context::getContext()->language->iso_code
        );

        return $translator->trans($id, $parameters);
    }
}
