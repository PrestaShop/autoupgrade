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

use PrestaShop\Module\AutoUpgrade\Exceptions\ProcessException;
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
        // Select on-the-fly modules that are in quarantine zone as well (Prefixed with the tag).
        $sql = 'SELECT REPLACE(`name`, "' . QuarantineZone::DISABLED_BY_SAFE_MODE . '", "") as name, version FROM ' . _DB_PREFIX_ . 'module';

        if (!empty($filterOnModuleNames)) {
            $sql .= ' WHERE name IN ("' . implode('", "', $filterOnModuleNames) . '")';
        }

        $sql .= ' ORDER BY `name`';

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * list modules to upgrade and save them in a serialized array in $this->toUpgradeModuleList.
     *
     * @return array<array{name:string, currentVersion:string}> Module available on the local filesystem and installed
     *
     * @throws ProcessException
     */
    public function listModulesPresentInFolderAndInstalled(): array
    {
        $list = [];
        $dir = $this->modulesPath;

        if (!is_dir($dir)) {
            throw (new ProcessException($this->translator->trans('%dir% does not exist or is not a directory.', ['%dir%' => $dir])))->addQuickInfo($this->translator->trans('%s does not exist or is not a directory.', [$dir]))->setSeverity(ProcessException::SEVERITY_ERROR);
        }

        foreach ($this->getInstalledVersionOfModules() as $moduleInstalled) {
            // We don't update autoupgrade module
            if ($moduleInstalled['name'] === 'autoupgrade') {
                continue;
            }
            if (!$this->isFolderContainingModule($dir . $moduleInstalled['name'])) {
                continue;
            }

            $list[] = [
                'name' => $moduleInstalled['name'],
                'currentVersion' => $moduleInstalled['version'],
            ];
        }

        return $list;
    }

    /**
     * Check the folder matches the contents of a module.
     * The file config.xml is not checked because it is not mandatory for a working module.
     */
    public function isFolderContainingModule(string $folder): bool
    {
        if (!is_dir($folder)) {
            return false;
        }

        $expectedModuleName = basename($folder);
        // A valid module contains a file mymodule/mymodule.php
        return is_file($folder . DIRECTORY_SEPARATOR . $expectedModuleName . '.php');
    }

    /**
     * Loads the config.xml from a module folder and get the version stored in there.
     */
    public function getVersionFromConfigXmlInFolder(string $moduleFolder): ?string
    {
        $configXmlPath = $moduleFolder . DIRECTORY_SEPARATOR . 'config.xml';
        if (!file_exists($configXmlPath)) {
            return null;
        }

        $xml = simplexml_load_file($configXmlPath);
        if (!$xml || empty($xml->version)) {
            return null;
        }

        return $xml->version;
    }
}
