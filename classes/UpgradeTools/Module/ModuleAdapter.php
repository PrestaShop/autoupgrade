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

    /** @var \PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface */
    private $commandBus;

    public function __construct(Translator $translator, string $modulesPath, SymfonyAdapter $symfonyAdapter)
    {
        $this->translator = $translator;
        $this->modulesPath = $modulesPath;
        $this->symfonyAdapter = $symfonyAdapter;
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
     * @param string[]|null $filterOnModuleNames
     *
     * @return array<array{name:string, version:string}>
     */
    public function getInstalledVersionOfModules(?array $filterOnModuleNames = null): array
    {
        $sql = 'SELECT name, version FROM ' . _DB_PREFIX_ . 'module';

        if (!empty($filterOnModuleNames)) {
            $sql .= ' WHERE name IN ("' . implode('", "', $filterOnModuleNames) . '")';
        }

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * list modules to upgrade and save them in a serialized array in $this->toUpgradeModuleList.
     *
     * @return array<array{name:string, currentVersion:string}> Module available on the local filesystem and installed
     *
     * @throws UpgradeException
     */
    public function listModulesPresentInFolderAndInstalled(): array
    {
        $list = [];
        $dir = $this->modulesPath;

        if (!is_dir($dir)) {
            throw (new UpgradeException($this->translator->trans('%dir% does not exist or is not a directory.', ['%dir%' => $dir])))->addQuickInfo($this->translator->trans('%s does not exist or is not a directory.', [$dir]))->setSeverity(UpgradeException::SEVERITY_ERROR);
        }

        foreach ($this->getInstalledVersionOfModules() as $moduleInstalled) {
            // We don't update autoupgrade module
            if ($moduleInstalled['name'] === 'autoupgrade') {
                continue;
            }
            // We have a file modules/mymodule
            if (is_file($dir . $moduleInstalled['name'])) {
                continue;
            }
            // We don't have a file modules/mymodule/config.xml
            if (!is_file($dir . $moduleInstalled['name'] . DIRECTORY_SEPARATOR . 'config.xml')) {
                continue;
            }
            // We don't have a file modules/mymodule/mymodule.php
            if (!is_file($dir . $moduleInstalled['name'] . DIRECTORY_SEPARATOR . $moduleInstalled['name'] . '.php')) {
                continue;
            }

            $list[] = [
                'name' => $moduleInstalled['name'],
                'currentVersion' => $moduleInstalled['version'],
            ];
        }

        return $list;
    }
}
