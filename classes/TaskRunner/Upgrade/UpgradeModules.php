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

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\UpgradeException;

/**
 * Upgrade all partners modules according to the installed prestashop version.
 */
class UpgradeModules extends AbstractTask
{
    public function run()
    {
        $start_time = time();
        if (!$this->container->getFileConfigurationStorage()->exists(UpgradeFileNames::MODULES_TO_UPGRADE_LIST)) {
            return $this->warmUp();
        }

        $this->next = 'upgradeModules';
        $listModules = $this->container->getFileConfigurationStorage()->load(UpgradeFileNames::MODULES_TO_UPGRADE_LIST);

        if (!is_array($listModules)) {
            $this->next = 'upgradeComplete';
            $this->container->getState()->setWarningExists(true);
            $this->logger->error($this->translator->trans('listModules is not an array. No module has been updated.', [], 'Modules.Autoupgrade.Admin'));

            return true;
        }

        // add local modules that we want to upgrade to the list
        $localModules = $this->getLocalModules();
        if (!empty($localModules)) {
            foreach ($localModules as $currentLocalModule) {
                $listModules[$currentLocalModule['name']] = [
                    'id' => $currentLocalModule['id_module'],
                    'name' => $currentLocalModule['name'],
                    'is_local' => true,
                ];
            }
        }

        // module list
        if (count($listModules) > 0) {
            do {
                $module_info = array_pop($listModules);
                try {
                    $this->logger->debug($this->translator->trans('Upgrading module %module%...', ['%module%' => $module_info['name']], 'Modules.Autoupgrade.Admin'));
                    $this->container->getModuleAdapter()->upgradeModule($module_info['id'], $module_info['name'], !empty($module_info['is_local']));
                    $this->logger->debug($this->translator->trans('The files of module %s have been upgraded.', [$module_info['name']], 'Modules.Autoupgrade.Admin'));
                } catch (UpgradeException $e) {
                    $this->handleException($e);
                    if ($e->getSeverity() === UpgradeException::SEVERITY_ERROR) {
                        return false;
                    }
                }
                $time_elapsed = time() - $start_time;
            } while (($time_elapsed < $this->container->getUpgradeConfiguration()->getTimePerCall()) && count($listModules) > 0);

            $modules_left = count($listModules);
            $this->container->getFileConfigurationStorage()->save($listModules, UpgradeFileNames::MODULES_TO_UPGRADE_LIST);
            unset($listModules);

            $this->next = 'upgradeModules';
            if ($modules_left) {
                $this->logger->info($this->translator->trans('%s modules left to upgrade.', [$modules_left], 'Modules.Autoupgrade.Admin'));
            }
            $this->stepDone = false;
        } else {
            $this->stepDone = true;
            $this->status = 'ok';
            $this->next = 'cleanDatabase';
            $this->logger->info($this->translator->trans('Addons modules files have been upgraded.', [], 'Modules.Autoupgrade.Admin'));

            return true;
        }

        return true;
    }

    /**
     * Get the list of module zips in admin/autoupgrade/modules
     * These zips will be used to upgrade related modules instead of using distant zips on addons
     *
     * @return array
     */
    private function getLocalModules()
    {
        $localModuleDir = sprintf(
            '%s%sautoupgrade%smodules',
            _PS_ADMIN_DIR_,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $zipFileNames = [];

        $zipFiles = glob($localModuleDir . DIRECTORY_SEPARATOR . '*.zip');

        if (empty($zipFiles)) {
            return [];
        }

        foreach ($zipFiles as $zipFile) {
            $zipFileNames[] = pSQL(pathinfo($zipFile, PATHINFO_FILENAME));
        }

        $sql = sprintf(
            "SELECT id_module, name FROM %smodule WHERE name IN ('%s')",
            _DB_PREFIX_,
            implode("','", $zipFileNames)
        );

        return \Db::getInstance()->executeS($sql);
    }

    public function warmUp()
    {
        try {
            $modulesToUpgrade = $this->container->getModuleAdapter()->listModulesToUpgrade(
                $this->container->getState()->getModules_addons(),
                $this->container->getState()->getModulesVersions()
            );
            $modulesToUpgrade = array_reverse($modulesToUpgrade);
            $this->container->getFileConfigurationStorage()->save($modulesToUpgrade, UpgradeFileNames::MODULES_TO_UPGRADE_LIST);
        } catch (UpgradeException $e) {
            $this->handleException($e);

            return false;
        }

        $total_modules_to_upgrade = count($modulesToUpgrade);
        if ($total_modules_to_upgrade) {
            $this->logger->info($this->translator->trans('%s modules will be upgraded.', [$total_modules_to_upgrade], 'Modules.Autoupgrade.Admin'));
        }

        $this->stepDone = false;
        $this->next = 'upgradeModules';

        return true;
    }

    private function handleException(UpgradeException $e)
    {
        foreach ($e->getQuickInfos() as $log) {
            $this->logger->debug($log);
        }
        if ($e->getSeverity() === UpgradeException::SEVERITY_ERROR) {
            $this->next = 'error';
            $this->error = true;
            $this->logger->error($e->getMessage());
        }
        if ($e->getSeverity() === UpgradeException::SEVERITY_WARNING) {
            $this->logger->warning($e->getMessage());
        }
    }
}
