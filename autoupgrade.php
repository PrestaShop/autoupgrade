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
        $this->version = '4.15.0';
        $this->need_instance = 1;

        $this->bootstrap = true;
        parent::__construct();

        $this->multishop_context = Shop::CONTEXT_ALL;

        if (!defined('_PS_ADMIN_DIR_')) {
            if (defined('PS_ADMIN_DIR')) {
                define('_PS_ADMIN_DIR_', PS_ADMIN_DIR);
            } else {
                $this->_errors[] = $this->trans('This version of PrestaShop cannot be upgraded: the PS_ADMIN_DIR constant is missing.', [], 'Modules.Autoupgrade.Admin');
            }
        }

        $this->displayName = $this->trans('1-Click Upgrade', [], 'Modules.Autoupgrade.Admin');
        $this->description = $this->trans('Upgrade to the latest version of PrestaShop in a few clicks, thanks to this automated method.', [], 'Modules.Autoupgrade.Admin');

        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        if (50600 > PHP_VERSION_ID) {
            $this->_errors[] = $this->trans('This version of 1-click upgrade requires PHP 5.6 to work properly. Please upgrade your server configuration.', [], 'Modules.Autoupgrade.Admin');

            return false;
        }

        // Before creating a new tab "AdminSelfUpgrade" we need to remove any existing "AdminUpgrade" tab (present in v1.4.4.0 and v1.4.4.1)
        if ($id_tab = Tab::getIdFromClassName('AdminUpgrade')) {
            $tab = new Tab((int) $id_tab);
            if (!$tab->delete()) {
                $this->_errors[] = $this->trans('Unable to delete outdated "AdminUpgrade" tab (tab ID: %idtab%).', ['%idtab%' => (int) $id_tab], 'Modules.Autoupgrade.Admin');
            }
        }

        // If the "AdminSelfUpgrade" tab does not exist yet, create it
        if (!$id_tab = Tab::getIdFromClassName('AdminSelfUpgrade')) {
            $tab = new Tab();
            $tab->class_name = 'AdminSelfUpgrade';
            $tab->module = 'autoupgrade';
            $tab->id_parent = (int) Tab::getIdFromClassName('AdminTools');
            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = '1-Click Upgrade';
            }
            if (!$tab->save()) {
                return $this->_abortInstall($this->trans('Unable to create the "AdminSelfUpgrade" tab', [], 'Modules.Autoupgrade.Admin'));
            }
            if (!@copy(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logo.gif', _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 't' . DIRECTORY_SEPARATOR . 'AdminSelfUpgrade.gif')) {
                return $this->_abortInstall($this->trans('Unable to copy logo.gif in %s', [_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 't' . DIRECTORY_SEPARATOR], 'Modules.Autoupgrade.Admin'));
            }
        } else {
            $tab = new Tab((int) $id_tab);
        }

        // Update the "AdminSelfUpgrade" tab id in database or exit
        if (Validate::isLoadedObject($tab)) {
            Configuration::updateValue('PS_AUTOUPDATE_MODULE_IDTAB', (int) $tab->id);
        } else {
            return $this->_abortInstall($this->trans('Unable to load the "AdminSelfUpgrade" tab', [], 'Modules.Autoupgrade.Admin'));
        }

        return parent::install() && $this->registerHookAndSetToTop('dashboardZoneOne');
    }

    public function uninstall()
    {
        // Delete the 1-click upgrade Back-office tab
        if ($id_tab = Tab::getIdFromClassName('AdminSelfUpgrade')) {
            $tab = new Tab((int) $id_tab);
            $tab->delete();
        }

        // Remove the 1-click upgrade working directory
        self::_removeDirectory(_PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'autoupgrade');

        return parent::uninstall();
    }

    /**
     * Register the current module to a given hook and moves it at the first position.
     *
     * @param string $hookName
     *
     * @return bool
     */
    public function registerHookAndSetToTop($hookName)
    {
        return $this->registerHook($hookName) && $this->updatePosition((int) Hook::getIdByName($hookName), false);
    }

    public function hookDashboardZoneOne($params)
    {
        // Display panel if PHP is not supported by the community
        require_once __DIR__ . '/vendor/autoload.php';

        $upgradeContainer = new \PrestaShop\Module\AutoUpgrade\UpgradeContainer(_PS_ROOT_DIR_, _PS_ADMIN_DIR_);
        $upgrader = $upgradeContainer->getUpgrader();
        $upgradeSelfCheck = new \PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck(
            $upgrader,
            _PS_ROOT_DIR_,
            _PS_ADMIN_DIR_,
            __DIR__
        );

        $upgradeNotice = $upgradeSelfCheck->isPhpUpgradeRequired();
        if (false === $upgradeNotice) {
            return '';
        }

        $this->context->controller->addCSS($this->_path . '/css/styles.css');
        $this->context->controller->addJS($this->_path . '/js/dashboard.js');

        $this->context->smarty->assign([
            'ignore_link' => Context::getContext()->link->getAdminLink('AdminSelfUpgrade') . '&ignorePhpOutdated=1',
            'learn_more_link' => 'http://build.prestashop.com/news/announcing-end-of-support-for-obsolete-php-versions/',
        ]);

        return $this->context->smarty->fetch($this->local_path . 'views/templates/hook/dashboard_zone_one.tpl');
    }

    public function getContent()
    {
        global $cookie;
        header('Location: index.php?tab=AdminSelfUpgrade&token=' . md5(pSQL(_COOKIE_KEY_ . 'AdminSelfUpgrade' . (int) Tab::getIdFromClassName('AdminSelfUpgrade') . (int) $cookie->id_employee)));
        exit;
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
     * @param array $parameters
     * @param string $domain
     * @param string $locale
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        require_once _PS_ROOT_DIR_ . '/modules/autoupgrade/classes/UpgradeTools/Translator.php';

        $translator = new \PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator(__CLASS__);

        return $translator->trans($id, $parameters, $domain, $locale);
    }
}
