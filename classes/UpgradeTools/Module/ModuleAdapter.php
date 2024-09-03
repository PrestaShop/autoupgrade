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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\SymfonyAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use PrestaShop\PrestaShop\Adapter\Module\Repository\ModuleRepository;

class ModuleAdapter
{
    /** @var Translator */
    private $translator;
    /** @var string */
    private $modulesPath;

    /**
     * @var SymfonyAdapter
     */
    private $symfonyAdapter;

    /** @var \PrestaShop\PrestaShop\Adapter\Module\ModuleDataUpdater */
    private $moduleDataUpdater;

    /** @var \PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface */
    private $commandBus;

    public function __construct(Translator $translator, string $modulesPath, SymfonyAdapter $symfonyAdapter)
    {
        $this->translator = $translator;
        $this->modulesPath = $modulesPath;
        $this->symfonyAdapter = $symfonyAdapter;
    }

    /**
     * Available only from 1.7. Can't be called on PS 1.6.
     *
     * @return \PrestaShop\PrestaShop\Adapter\Module\ModuleDataUpdater
     */
    public function getModuleDataUpdater()
    {
        if (null === $this->moduleDataUpdater) {
            $this->moduleDataUpdater = $this->symfonyAdapter
                ->initKernel()
                ->getContainer()
                ->get('prestashop.core.module.updater');
        }

        return $this->moduleDataUpdater;
    }

    /**
     * Available only since 1.7.6.0 Can't be called on PS 1.6.
     *
     * @return \PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface
     */
    public function getCommandBus()
    {
        if (null === $this->commandBus) {
            $this->commandBus = $this->symfonyAdapter
                ->initKernel()
                ->getContainer()
                ->get('prestashop.core.command_bus');
        }

        return $this->commandBus;
    }

    /**
     * Upgrade action, disabling all modules not made by PrestaShop.
     *
     * It seems the 1.6 version of is the safest, as it does not actually load the modules.
     *
     * @param string $pathToUpgradeScripts Path to the PHP Upgrade scripts
     */
    public function disableNonNativeModules(string $pathToUpgradeScripts): void
    {
        require_once $pathToUpgradeScripts . 'php/deactivate_custom_modules.php';
        deactivate_custom_modules();
    }

    public function disableNonNativeModules80(string $pathToUpgradeScripts, ModuleRepository $moduleRepository): void
    {
        require_once $pathToUpgradeScripts . 'php/deactivate_custom_modules.php';
        deactivate_custom_modules80($moduleRepository);
    }

    /**
     * list modules to upgrade and save them in a serialized array in $this->toUpgradeModuleList.
     *
     * @param array<string, string> $modulesFromAddons Modules available on the marketplace for download
     * @param array<string, string> $modulesVersions
     *
     * @return array<string, array{'id':string, 'name':string}> Module available on the local filesystem and on the marketplace
     *
     * @throws UpgradeException
     */
    public function listModulesToUpgrade(array $modulesFromAddons, array $modulesVersions): array
    {
        $list = [];
        $dir = $this->modulesPath;

        if (!is_dir($dir)) {
            throw (new UpgradeException($this->translator->trans('[ERROR] %dir% does not exist or is not a directory.', ['%dir%' => $dir])))->addQuickInfo($this->translator->trans('[ERROR] %s does not exist or is not a directory.', [$dir]))->setSeverity(UpgradeException::SEVERITY_ERROR);
        }

        foreach (scandir($dir) as $module_name) {
            // We don't update autoupgrade module
            if ($module_name === 'autoupgrade') {
                continue;
            }
            // We have a file modules/mymodule
            if (is_file($dir . $module_name)) {
                continue;
            }
            // We don't have a file modules/mymodule/config.xml
            if (!is_file($dir . $module_name . DIRECTORY_SEPARATOR . 'config.xml')) {
                continue;
            }
            // We don't have a file modules/mymodule/mymodule.php
            if (!is_file($dir . $module_name . DIRECTORY_SEPARATOR . $module_name . '.php')) {
                continue;
            }
            $id_addons = array_search($module_name, $modulesFromAddons);
            // We don't find the module on Addons
            if (false === $id_addons) {
                continue;
            }
            $configXML = file_get_contents($dir . $module_name . DIRECTORY_SEPARATOR . 'config.xml');
            $moduleXML = simplexml_load_string($configXML);
            // The module installed has a higher version than this available on Addons
            if (version_compare((string) $moduleXML->version, $modulesVersions[$id_addons]) >= 0) {
                continue;
            }
            $list[$module_name] = [
                'id' => $id_addons,
                'name' => $module_name,
            ];
        }

        return $list;
    }
}
