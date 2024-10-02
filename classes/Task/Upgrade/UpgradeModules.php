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

namespace PrestaShop\Module\AutoUpgrade\Task\Upgrade;

use Exception;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Progress\Backlog;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleDownloader;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleDownloaderContext;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleMigration;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleMigrationContext;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleUnzipper;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleUnzipperContext;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleVersionAdapter;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSourceAggregate;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Upgrade all partners modules according to the installed prestashop version.
 */
class UpgradeModules extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_UPDATE;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        if (!$this->container->getFileConfigurationStorage()->exists(UpgradeFileNames::MODULES_TO_UPGRADE_LIST)) {
            return $this->warmUp();
        }

        $listModules = Backlog::fromContents($this->container->getFileConfigurationStorage()->load(UpgradeFileNames::MODULES_TO_UPGRADE_LIST));

        $modulesPath = $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR;

        $moduleSourceList = new ModuleSourceAggregate($this->container->getModuleSourceProviders());
        $moduleDownloader = new ModuleDownloader($this->translator, $this->logger, $this->container->getProperty(UpgradeContainer::TMP_PATH));
        $moduleUnzipper = new ModuleUnzipper($this->translator, $this->container->getZipAction(), $modulesPath);
        $moduleMigration = new ModuleMigration($this->translator, $this->logger);

        if ($listModules->getRemainingTotal()) {
            $moduleInfos = $listModules->getNext();

            try {
                $this->logger->debug($this->translator->trans('Checking updates of module %module%...', ['%module%' => $moduleInfos['name']]));

                $moduleDownloaderContext = new ModuleDownloaderContext($moduleInfos);
                $moduleSourceList->setSourcesIn($moduleDownloaderContext);

                if (empty($moduleDownloaderContext->getUpdateSources())) {
                    $this->logger->debug($this->translator->trans('Module %module% is up-to-date.', ['%module%' => $moduleInfos['name']]));
                } else {
                    $moduleDownloader->downloadModule($moduleDownloaderContext);

                    $moduleUnzipperContext = new ModuleUnzipperContext($moduleDownloaderContext->getPathToModuleUpdate(), $moduleInfos['name']);
                    $moduleUnzipper->unzipModule($moduleUnzipperContext);

                    $dbVersion = (new ModuleVersionAdapter())->get($moduleInfos['name']);
                    $module = \Module::getInstanceByName($moduleInfos['name']);

                    if (!($module instanceof \Module)) {
                        throw (new UpgradeException($this->translator->trans('[WARNING] Error when trying to retrieve module %s instance.', [$moduleInfos['name']])))->setSeverity(UpgradeException::SEVERITY_WARNING);
                    }

                    $moduleMigrationContext = new ModuleMigrationContext($module, $dbVersion);

                    if (!$moduleMigration->needMigration($moduleMigrationContext)) {
                        $this->logger->info($this->translator->trans('Module %s does not need to be migrated. Module is up to date.', [$moduleInfos['name']]));
                    } else {
                        $moduleMigration->runMigration($moduleMigrationContext);
                    }
                    $moduleMigration->saveVersionInDb($moduleMigrationContext);
                }
            } catch (UpgradeException $e) {
                $this->handleException($e);
                if ($e->getSeverity() === UpgradeException::SEVERITY_ERROR) {
                    return ExitCode::FAIL;
                }
            } finally {
                // Cleanup of module assets
                if (!empty($moduleDownloaderContext) && !empty($moduleDownloaderContext->getPathToModuleUpdate())) {
                    (new Filesystem())->remove([$moduleDownloaderContext->getPathToModuleUpdate()]);
                }
            }
        }

        $modules_left = $listModules->getRemainingTotal();
        $this->container->getState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->computePercentage($listModules, self::class, CleanDatabase::class)
        );
        $this->container->getFileConfigurationStorage()->save($listModules->dump(), UpgradeFileNames::MODULES_TO_UPGRADE_LIST);

        if ($modules_left) {
            $this->stepDone = false;
            $this->next = 'upgradeModules';
            $this->logger->info($this->translator->trans('%s modules left to update.', [$modules_left]));
        } else {
            $this->stepDone = true;
            $this->status = 'ok';
            $this->next = 'cleanDatabase';
            $this->logger->info($this->translator->trans('All modules have been updated.'));
        }

        return ExitCode::SUCCESS;
    }

    public function warmUp(): int
    {
        $this->container->getState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        try {
            $modulesToUpgrade = $this->container->getModuleAdapter()->listModulesPresentInFolderAndInstalled();
            $modulesToUpgrade = array_reverse($modulesToUpgrade);
            $total_modules_to_upgrade = count($modulesToUpgrade);

            $this->container->getFileConfigurationStorage()->save(
                (new Backlog($modulesToUpgrade, $total_modules_to_upgrade))->dump(),
                UpgradeFileNames::MODULES_TO_UPGRADE_LIST
            );
        } catch (UpgradeException $e) {
            $this->handleException($e);

            return ExitCode::FAIL;
        }

        if ($total_modules_to_upgrade) {
            $this->logger->info($this->translator->trans('%s modules will be upgraded.', [$total_modules_to_upgrade]));
        }

        $this->stepDone = false;
        $this->next = 'upgradeModules';

        return ExitCode::SUCCESS;
    }

    private function handleException(UpgradeException $e): void
    {
        if ($e->getSeverity() === UpgradeException::SEVERITY_ERROR) {
            $this->next = 'error';
            $this->setErrorFlag();
            $this->logger->error($e->getMessage());
        }
        if ($e->getSeverity() === UpgradeException::SEVERITY_WARNING) {
            $this->logger->warning($e->getMessage());
        }

        foreach ($e->getQuickInfos() as $log) {
            $this->logger->warning($log);
        }
    }
}
