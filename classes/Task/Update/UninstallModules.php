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

namespace PrestaShop\Module\AutoUpgrade\Task\Update;

use Exception;
use PrestaShop\Module\AutoUpgrade\Exceptions\ProcessException;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Progress\Backlog;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;

class UninstallModules extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_UPDATE;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        if (!$this->container->getFileStorage()->exists(UpgradeFileNames::MODULES_TO_UNINSTALL_LIST)) {
            return $this->warmUp();
        }

        $listModules = Backlog::fromContents($this->container->getFileStorage()->load(UpgradeFileNames::MODULES_TO_UNINSTALL_LIST));

        if ($listModules->getRemainingTotal()) {
            $moduleName = $listModules->getNext();

            try {
                $this->logger->info($this->translator->trans('Uninstalling module %module%...', ['%module%' => $moduleName]));

                $module = \Module::getInstanceByName($moduleName);

                if (!($module instanceof \Module)) {
                    throw (new ProcessException($this->translator->trans('Retrieving the module instance of %s failed.', [$moduleName])))->setSeverity(ProcessException::SEVERITY_WARNING);
                }

                try {
                    $module->uninstall();
                } catch (\Throwable $e) {
                    throw (new ProcessException($this->translator->trans('An error occurred while uninstalling the module %s. Uninstall it manually then try again.', [$moduleName])))->addQuickInfo($e)->setSeverity(ProcessException::SEVERITY_ERROR);
                }

                $this->logger->info($this->translator->trans('Module %module% is uninstalled.', ['%module%' => $moduleName]));
            } catch (ProcessException $e) {
                $this->handleException($e);
                if ($e->getSeverity() === ProcessException::SEVERITY_ERROR) {
                    return ExitCode::FAIL;
                }
            }
        }

        $modulesLeft = $listModules->getRemainingTotal();
        $this->container->getUpdateState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->computePercentage($listModules, self::class, UpdateFiles::class)
        );
        $this->container->getFileStorage()->save($listModules->dump(), UpgradeFileNames::MODULES_TO_UNINSTALL_LIST);

        if ($modulesLeft) {
            $this->stepDone = false;
            $this->next = TaskName::TASK_UNINSTALL_MODULES;
            $this->logger->info($this->translator->trans('%s modules left to uninstall.', [$modulesLeft]));
        } else {
            $this->stepDone = true;
            $this->status = 'ok';
            $this->next = TaskName::TASK_UPDATE_FILES;
            $this->logger->info($this->translator->trans('All modules have been uninstalled.'));
        }

        return ExitCode::SUCCESS;
    }

    /**
     * @throws Exception
     */
    public function init(): void
    {
        $this->container->initPrestaShopCore();
        // Container may be needed to uninstall if the module loads services.
        $this->container->getSymfonyAdapter()->initKernel();
    }

    /**
     * @throws Exception
     */
    private function warmUp(): int
    {
        if ($this->container->getUpdateState()->getSkipUninstallModule()) {
            $this->stepDone = true;
            $this->status = 'ok';
            $this->next = TaskName::TASK_UPDATE_FILES;
            if (!$this->container->getUpdateConfiguration()->shouldUninstallNonCompatibleModules()) {
                $this->logger->info($this->translator->trans('Uninstalling incompatible modules is disabled. Skipping to the next step.'));
            } else {
                $this->logger->info($this->translator->trans('Since this version of PrestaShop is not released to the public, the module compatibility check for uninstallation cannot be performed. Skipping to the next step.'));
            }

            return ExitCode::SUCCESS;
        }

        $this->container->getUpdateState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $targetVersion = $this->container->getUpdateState()->getDestinationVersion();

        $this->logger->info($this->translator->trans('Checking modules compatibility with PrestaShop %s and uninstalling incompatible ones.', [$targetVersion]));

        try {
            $modulesList = $this->container->getModuleAdapter()->listModulesPresentInFolderAndInstalled();
            $modulesList = array_reverse($modulesList);

            $moduleToUpgradeList = $this->container->getFileStorage()->load(UpgradeFileNames::MODULES_TO_UPGRADE_LIST);
            $moduleToUpgradeNames = array_column($moduleToUpgradeList, 'name');

            $modulesList = array_filter($modulesList, function ($module) use ($moduleToUpgradeNames) {
                return !in_array($module['name'], $moduleToUpgradeNames);
            });

            $checkResult = $this->container->getModuleCompatibilityChecker()->getModulesRequiringAttention(
                $modulesList,
                $targetVersion,
                $this->container->getUpdateState()->getCurrentVersion()
            );

            foreach ($checkResult['uncertain_modules'] as $moduleName) {
                $this->logger->warning($this->translator->trans('Could not check compatibility of module %s with the Marketplace API.', [$moduleName]));
                $this->container->getUpdateState()->setWarningDetected(true);
            }

            $modulesToUninstallList = $checkResult['incompatible_modules'];
            $totalModulesToUninstall = count($modulesToUninstallList);

            $this->container->getFileStorage()->save(
                [
                    'backlog' => $modulesToUninstallList,
                    'initialTotal' => $totalModulesToUninstall,
                ],
                UpgradeFileNames::MODULES_TO_UNINSTALL_LIST
            );
        } catch (ProcessException $e) {
            $this->handleException($e);

            return ExitCode::FAIL;
        }

        if ($totalModulesToUninstall) {
            $this->logger->info($this->translator->trans('%nbOfModules% incompatible modules with PrestaShop %version% will be uninstalled.', ['%nbOfModules%' => $totalModulesToUninstall, '%version%' => $targetVersion]));
        }

        $this->stepDone = false;
        $this->next = TaskName::TASK_UNINSTALL_MODULES;

        return ExitCode::SUCCESS;
    }
}
