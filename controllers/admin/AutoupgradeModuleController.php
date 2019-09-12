<?php
/**
 * 2007-2019 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
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
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
use PrestaShop\Module\AutoUpgrade\Tools14;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\Module\ModuleDisabler;
use PrestaShop\Module\AutoUpgrade\Module\ModuleRepository;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

require_once _PS_ROOT_DIR_ . '/modules/autoupgrade/vendor/autoload.php';

class AutoupgradeModuleController extends ModuleAdminController
{
    /**
     * @var UpgradeContainer
     */
    private $upgradeContainer;

    /**
     * @var ModuleRepository
     */
    private $moduleRepository;

    /**
     * @var ModuleDisabler
     */
    private $moduleDisabler;

    /**
     * This needs to be protected for compatibility with 1.7+ versions
     *
     * @var Translator
     */
    protected $translator;

    public function __construct()
    {
        parent::__construct();
        $this->upgradeContainer = new UpgradeContainer(_PS_ROOT_DIR_, realpath(_PS_ADMIN_DIR_));
        $this->moduleRepository = $this->upgradeContainer->getModuleAdapter()->getModuleRepository();
        $this->moduleDisabler = $this->upgradeContainer->getModuleAdapter()->getModuleDisabler();
    }

    public function postProcess()
    {
        if (Tools14::isSubmit('disableAllCustomModules')) {
            $this->disableAllCustomModules();
        }

        if (Tools14::isSubmit('disableCustomModule')) {
            $moduleName = Tools14::getValue('disableCustomModule');
            if (!in_array($moduleName, $this->getCustomModules())) {
                $this->_errors[] = $this->trans('Unable to find custom module %s', array($moduleName), 'Modules.Autoupgrade.Admin');
            } else {
                $this->moduleDisabler->disableModuleFromDatabase($moduleName);
                $this->moduleDisabler->disableModuleFromDisk($moduleName);
            }
        }

        if (Tools14::isSubmit('enableAllCustomModules')) {
            $this->enableAllCustomModules();
        }

        if (Tools14::isSubmit('enableCustomModule')) {
            $moduleName = Tools14::getValue('enableCustomModule');
            if (!in_array($moduleName, $this->moduleRepository->getDisabledModulesOnDisk())) {
                $this->_errors[] = $this->trans('Unable to find disabled custom module %s', array($moduleName), 'Modules.Autoupgrade.Admin');
            } else {
                $this->moduleDisabler->enableModuleFromDisk($moduleName);
                $this->moduleDisabler->enableModuleFromDatabase($moduleName);
            }
        }

        parent::postProcess();
    }

    public function display()
    {
        if (!empty($this->_errors)) {
            return $this->ajaxDie(json_encode([
                'error' => true,
                'errors' => $this->_errors,
            ]));
        }
        $version = Tools14::getIsset('version') ? Tools14::getValue('version') : _PS_VERSION_;

        return $this->ajaxDie(json_encode([
            'error' => false,
            'modules_on_disk' => $this->moduleRepository->getModulesOnDisk(),
            'disabled_modules' => $this->moduleRepository->getDisabledModulesOnDisk(),
            'native_modules' => $this->moduleRepository->getNativeModulesForVersion($version),
            'custom_modules' => $this->getCustomModules(),
        ]));
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
        if (null === $this->translator) {
            $this->translator = new Translator(__CLASS__);
        }

        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Disable custom module on disk and database
     */
    private function disableAllCustomModules()
    {
        $customModules = $this->getCustomModules();
        foreach ($customModules as $moduleName) {
            $this->moduleDisabler->disableModuleFromDatabase($moduleName);
            $this->moduleDisabler->disableModuleFromDisk($moduleName);
        }
    }

    /**
     * Enable custom module on disk and database
     */
    private function enableAllCustomModules()
    {
        $customModules = $this->moduleRepository->getDisabledModulesOnDisk();
        foreach ($customModules as $moduleName) {
            $this->moduleDisabler->enableModuleFromDisk($moduleName);
            $this->moduleDisabler->enableModuleFromDatabase($moduleName);
        }
    }

    /**
     * @return array
     */
    private function getCustomModules()
    {
        $version = Tools14::getIsset('version') ? Tools14::getValue('version') : _PS_VERSION_;

        return $this->moduleRepository->getCustomModulesOnDisk([_PS_VERSION_, $version]);
    }
}
