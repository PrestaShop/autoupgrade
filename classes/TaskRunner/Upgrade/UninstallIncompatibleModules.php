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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade;

use Module;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeException;

/**
 * Uninstall modules that are not compatible with the version of PrestaShop being installed
 */
class UninstallIncompatibleModules extends AbstractTask
{
    const PS_VERSION_INCOMPATIBLE_MODULES = '1.7.0.0';

    // Modules incompatible with PrestaShop >= 1.7.x
    private $incompatibleModules = [
        'bankwire',
        'blockbanner',
        'blockcart',
        'blockcategories',
        'blockcms',
        'blockcmsinfo',
        'blockcontact',
        'blockcurrencies',
        'blocklanguages',
        'blocklayered',
        'blockmyaccount',
        'blocknewsletter',
        'blocksearch',
        'blocksocial',
        'blocktopmenu',
        'blockuserinfo',
        'cheque',
        'homefeatured',
        'homeslider',
        'onboarding',
        'socialsharing',
        'vatnumber',
        'blockadvertising',
        'blockbestsellers',
        'blockcustomerprivacy',
        'blocklink',
        'blockmanufacturer',
        'blocknewproducts',
        'blockpermanentlinks',
        'blockrss',
        'blocksharefb',
        'blockspecials',
        'blocksupplier',
        'blockviewed',
        'crossselling',
        'followup',
        'productscategory',
        'producttooltip',
        'mailalert',
        'blockcontactinfos',
        'blockfacebook',
        'blockmyaccountfooter',
        'blockpaymentlogo',
        'blockstore',
        'blocktags',
        'blockwishlist',
        'productpaymentlogos',
        'sendtoafriend',
        'themeconfigurator',
    ];

    public function run()
    {

        $this->next = 'upgradeFiles';
        if (
            version_compare(
                $this->container->getState()->getInstallVersion(),
                static::PS_VERSION_INCOMPATIBLE_MODULES,
                '<'
            )
        ) {
            $this->logger->info(
                $this->translator->trans(
                    'No incompatible module to uninstall. Now upgrading files...',
                    array(),
                    'Modules.Autoupgrade.Admin'
                )
            );

            return true;
        }

        $modulePath = $this->getModulePath();
        $this->assertModulePathIsValid($modulePath);

        $installedModules = Module::getModulesInstalled();
        foreach ($installedModules as $installedModule) {
            $moduleName = $installedModule['name'];
            if (
                in_array($moduleName, $this->incompatibleModules)
                && file_exists($modulePath . $moduleName . DIRECTORY_SEPARATOR . $moduleName . '.php')
            ) {
                $this->logger->info('Uninstalling module ' . $moduleName);
                $module = Module::getInstanceByName($moduleName);
                if ($module instanceof Module && !$module->uninstall()) {
                    $this->logger->warning('Unable to uninstall ' . $moduleName);
                }
            }
        }

        $this->logger->info(
            $this->translator->trans(
                'Incompatible modules uninstalled. Now upgrading files...',
                array(),
                'Modules.Autoupgrade.Admin'
            )
        );

        return true;
    }

    private function getModulePath()
    {
        return $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH)
            . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR;
    }

    private function assertModulePathIsValid($modulePath)
    {
        if (!is_dir($modulePath)) {
            throw (new UpgradeException(
                $this->translator->trans(
                    '[ERROR] %dir% does not exist or is not a directory.',
                    array('%dir%' => $modulePath),
                    'Modules.Autoupgrade.Admin'
                )
            ))
                ->addQuickInfo(
                    $this->translator->trans(
                        '[ERROR] %s does not exist or is not a directory.',
                        array($modulePath),
                        'Modules.Autoupgrade.Admin'
                    )
                )
                ->setSeverity(UpgradeException::SEVERITY_ERROR);
        }
    }
}
