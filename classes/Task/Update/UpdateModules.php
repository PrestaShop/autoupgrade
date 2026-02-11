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
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleMigration;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleMigrationContext;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleUnzipper;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleUnzipperContext;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleVersionAdapter;

/**
 * Upgrade all partners modules according to the installed prestashop version.
 */
class UpdateModules extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_UPDATE;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        if ($this->container->getUpdateState()->getProgressPercentage() < $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)) {
            return $this->warmUp();
        }

        $listModules = Backlog::fromContents($this->container->getFileStorage()->load(UpgradeFileNames::MODULES_TO_UPGRADE_LIST));

        $modulesPath = $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR;

        $moduleUnzipper = new ModuleUnzipper($this->translator, $this->container->getZipAction(), $modulesPath);
        $moduleMigration = new ModuleMigration($this->container->getFileSystem(), $this->translator, $this->logger);

        if ($listModules->getRemainingTotal()) {
            $moduleInfos = $listModules->getNext();

            try {
                $this->logger->debug($this->translator->trans('Starting update module %module%...', ['%module%' => $moduleInfos['name']]));

                $this->container->getQuarantineZone()->removeOne($moduleInfos['name']);

                $moduleUnzipperContext = new ModuleUnzipperContext($moduleInfos['pathToModuleUpdate'], $moduleInfos['name']);
                $moduleUnzipper->unzipModule($moduleUnzipperContext);

                $dbVersion = (new ModuleVersionAdapter())->get($moduleInfos['name']);
                $module = \Module::getInstanceByName($moduleInfos['name']);

                if (!($module instanceof \Module)) {
                    throw (new ProcessException($this->translator->trans('Retrieving the module instance of %s failed.', [$moduleInfos['name']])))->setSeverity(ProcessException::SEVERITY_WARNING);
                }

                $moduleMigrationContext = new ModuleMigrationContext($module, $dbVersion);

                if (!$moduleMigration->needMigration($moduleMigrationContext)) {
                    $this->logger->info($this->translator->trans('Module %s does not need to be migrated. Module is up to date.', [$moduleInfos['name']]));
                } else {
                    // Container may be needed to run upgrade scripts
                    $this->container->getSymfonyAdapter()->initKernel();

                    $moduleMigration->runMigration($moduleMigrationContext);
                }
                $moduleMigration->saveVersionInDb($moduleMigrationContext);
            } catch (ProcessException $e) {
                $this->handleException($e);
                if ($e->getSeverity() === ProcessException::SEVERITY_ERROR) {
                    return ExitCode::FAIL;
                }
            } finally {
                // Cleanup of module assets
                if (!empty($moduleInfos['pathToModuleUpdate'])) {
                    $this->container->getFileSystem()->remove([$moduleInfos['pathToModuleUpdate']]);
                }
            }
        }

        $modulesLeft = $listModules->getRemainingTotal();
        $this->container->getUpdateState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->computePercentage($listModules, self::class, CleanDatabase::class)
        );
        $this->container->getFileStorage()->save($listModules->dump(), UpgradeFileNames::MODULES_TO_UPGRADE_LIST);

        if ($modulesLeft) {
            $this->stepDone = false;
            $this->next = TaskName::TASK_UPDATE_MODULES;
            $this->logger->info($this->translator->trans('%s modules updates to apply.', [$modulesLeft]));
        } else {
            $this->doneStep();
        }

        return ExitCode::SUCCESS;
    }

    public function warmUp(): int
    {
        $this->container->getUpdateState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        if (!$this->container->getFileStorage()->exists(UpgradeFileNames::MODULES_TO_UPGRADE_LIST)) {
            $this->next = TaskName::TASK_ERROR;
            $this->setErrorFlag();
            $this->logger->error($this->translator->trans('The list of modules to upgrade is missing. Did you run the step DownloadModules?'));

            return ExitCode::FAIL;
        }

        $moduleToUpgradeBacklog = Backlog::fromContents($this->container->getFileStorage()->load(UpgradeFileNames::MODULES_TO_UPGRADE_LIST));

        if ($moduleToUpgradeBacklog->getInitialTotal()) {
            $this->logger->info($this->translator->trans('%s modules will be updated.', [$moduleToUpgradeBacklog->getInitialTotal()]));

            $this->stepDone = false;
            $this->next = TaskName::TASK_UPDATE_MODULES;
        } else {
            $this->doneStep();
        }

        return ExitCode::SUCCESS;
    }

    /**
     * @throws Exception
     */
    public function init(): void
    {
        $this->container->initPrestaShopCore();
    }

    private function doneStep(): void
    {
        // Remove all remaining modules from the quarantine
        $this->container->getQuarantineZone()->removeAll();

        $this->stepDone = true;
        $this->status = 'ok';
        $this->next = TaskName::TASK_CLEAN_DATABASE;
        $this->logger->info($this->translator->trans('All modules have been updated.'));
    }
}
