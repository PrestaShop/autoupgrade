<?php

/**
 * 2007-2016 PrestaShop.
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
class Autoupgrade extends Module
{
    public function __construct()
    {
        $this->name = 'autoupgrade';
        $this->tab = 'administration';
        $this->author = 'PrestaShop';
        $this->version = '4.10.1';
        $this->need_instance = 1;

        $this->bootstrap = true;
        parent::__construct();

        $this->multishop_context = Shop::CONTEXT_ALL;

        if (!defined('_PS_ADMIN_DIR_')) {
            if (defined('PS_ADMIN_DIR')) {
                define('_PS_ADMIN_DIR_', PS_ADMIN_DIR);
            } else {
                $this->_errors[] = $this->trans('This version of PrestaShop cannot be upgraded: the PS_ADMIN_DIR constant is missing.', array(), 'Modules.Autoupgrade.Admin');
            }
        }

        $this->displayName = $this->trans('1-Click Upgrade', array(), 'Modules.Autoupgrade.Admin');
        $this->description = $this->trans('Provides an automated method to upgrade your shop to the latest version of PrestaShop.', array(), 'Modules.Autoupgrade.Admin');

        $this->ps_versions_compliancy = array('min' => '1.6.0.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (50600 > PHP_VERSION_ID) {
            $this->_errors[] = $this->trans('This version of 1-click upgrade requires PHP 5.6 to work properly. Please upgrade your server configuration.', array(), 'Modules.Autoupgrade.Admin');

            return false;
        }

        if (defined('_PS_HOST_MODE_') && _PS_HOST_MODE_) {
            return false;
        }

        // Before creating a new tab "AdminSelfUpgrade" we need to remove any existing "AdminUpgrade" tab (present in v1.4.4.0 and v1.4.4.1)
        if ($id_tab = Tab::getIdFromClassName('AdminUpgrade')) {
            $tab = new Tab((int) $id_tab);
            if (!$tab->delete()) {
                $this->_errors[] = $this->trans('Unable to delete outdated "AdminUpgrade" tab (tab ID: %idtab%).', array('%idtab%' => (int) $id_tab), 'Modules.Autoupgrade.Admin');
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
                return $this->_abortInstall($this->trans('Unable to create the "AdminSelfUpgrade" tab', array(), 'Modules.Autoupgrade.Admin'));
            }
            if (!@copy(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logo.gif', _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 't' . DIRECTORY_SEPARATOR . 'AdminSelfUpgrade.gif')) {
                return $this->_abortInstall($this->trans('Unable to copy logo.gif in %s', array(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 't' . DIRECTORY_SEPARATOR), 'Modules.Autoupgrade.Admin'));
            }
        } else {
            $tab = new Tab((int) $id_tab);
        }

        // Update the "AdminSelfUpgrade" tab id in database or exit
        if (Validate::isLoadedObject($tab)) {
            Configuration::updateValue('PS_AUTOUPDATE_MODULE_IDTAB', (int) $tab->id);
        } else {
            return $this->_abortInstall($this->trans('Unable to load the "AdminSelfUpgrade" tab', array(), 'Modules.Autoupgrade.Admin'));
        }

        // Check that the 1-click upgrade working directory is existing or create it
        $autoupgrade_dir = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'autoupgrade';
        if (!file_exists($autoupgrade_dir) && !@mkdir($autoupgrade_dir)) {
            return $this->_abortInstall($this->trans('Unable to create the directory "%s"', array($autoupgrade_dir), 'Modules.Autoupgrade.Admin'));
        }

        // Make sure that the 1-click upgrade working directory is writeable
        if (!is_writable($autoupgrade_dir)) {
            return $this->_abortInstall($this->trans('Unable to write in the directory "%s"', array($autoupgrade_dir), 'Modules.Autoupgrade.Admin'));
        }

        // If a previous version of ajax-upgradetab.php exists, delete it
        if (file_exists($autoupgrade_dir . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php')) {
            @unlink($autoupgrade_dir . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php');
        }

        // Then, try to copy the newest version from the module's directory
        if (!@copy(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php', $autoupgrade_dir . DIRECTORY_SEPARATOR . 'ajax-upgradetab.php')) {
            return $this->_abortInstall($this->trans('Unable to copy ajax-upgradetab.php in %s', array($autoupgrade_dir), 'Modules.Autoupgrade.Admin'));
        }

        // Make sure that the XML config directory exists
        if (!file_exists(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml') &&
        !@mkdir(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml', 0775)) {
            return $this->_abortInstall($this->trans('Unable to create the directory "%s"', array(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml'), 'Modules.Autoupgrade.Admin'));
        } else {
            @chmod(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml', 0775);
        }

        // Create a dummy index.php file in the XML config directory to avoid directory listing
        if (!file_exists(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'index.php') &&
        (file_exists(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'index.php') &&
        !@copy(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'index.php', _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'index.php'))) {
            return $this->_abortInstall($this->trans('Unable to create the directory "%s"', array(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'xml'), 'Modules.Autoupgrade.Admin'));
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

        Configuration::deleteByName('PS_AUTOUP_IGNORE_REQS');
        Configuration::deleteByName('PS_AUTOUP_IGNORE_PHP_UPGRADE');

        return parent::uninstall();
    }

    /**
     * Register the current module to a given hook and moves it at the first position.
     *
     * @param string $hookName
     *
     * @return bool
     */
    private function registerHookAndSetToTop($hookName)
    {
        return $this->registerHook($hookName) && $this->updatePosition((int) Hook::getIdByName($hookName), 0);
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
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        require_once _PS_ROOT_DIR_ . '/modules/autoupgrade/classes/UpgradeTools/Translator.php';

        $translator = new \PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator(__CLASS__);

        return $translator->trans($id, $parameters, $domain, $locale);
    }
}
