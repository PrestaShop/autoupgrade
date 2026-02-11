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
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleDownloader;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleDownloaderContext;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\Source\ModuleSourceAggregate;

class DownloadModules extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_UPDATE;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        if (
            !$this->container->getFileStorage()->exists(UpgradeFileNames::MODULES_TO_DOWNLOAD_LIST)
            || !$this->container->getFileStorage()->exists(UpgradeFileNames::MODULES_TO_UPGRADE_LIST)
        ) {
            return $this->warmUp();
        }

        $listModules = Backlog::fromContents($this->container->getFileStorage()->load(UpgradeFileNames::MODULES_TO_DOWNLOAD_LIST));

        $moduleSourceList = new ModuleSourceAggregate($this->container->getModuleSourceProviders());
        $moduleDownloader = new ModuleDownloader($this->container->getDownloadService(), $this->translator, $this->logger, $this->container->getProperty(UpgradeContainer::TMP_MODULES_DIR));

        if ($listModules->getRemainingTotal()) {
            $moduleInfos = $listModules->getNext();

            try {
                $this->logger->debug($this->translator->trans('Checking updates of module %module%...', ['%module%' => $moduleInfos['name']]));

                $moduleDownloaderContext = new ModuleDownloaderContext($moduleInfos);
                $moduleSourceList->setSourcesIn($moduleDownloaderContext);

                if (empty($moduleDownloaderContext->getUpdateSources())) {
                    $this->logger->debug($this->translator->trans('No update available for %module%.', ['%module%' => $moduleInfos['name']]));
                } else {
                    $moduleDownloader->downloadModule($moduleDownloaderContext);

                    $moduleToUpgradeBacklog = Backlog::fromContents($this->container->getFileStorage()->load(UpgradeFileNames::MODULES_TO_UPGRADE_LIST))->dump();
                    $moduleToUpgradeInfos = [
                        'name' => $moduleInfos['name'],
                        'pathToModuleUpdate' => $moduleDownloaderContext->getPathToModuleUpdate(),
                    ];
                    $moduleToUpgradeBacklog['backlog'][] = $moduleToUpgradeInfos;
                    ++$moduleToUpgradeBacklog['initialTotal'];

                    $this->container->getFileStorage()->save(
                        $moduleToUpgradeBacklog,
                        UpgradeFileNames::MODULES_TO_UPGRADE_LIST
                    );
                }
            } catch (ProcessException $e) {
                $this->handleException($e);
                if ($e->getSeverity() === ProcessException::SEVERITY_ERROR) {
                    return ExitCode::FAIL;
                }
            }
        }

        $modulesLeft = $listModules->getRemainingTotal();
        $this->container->getUpdateState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->computePercentage($listModules, self::class, UninstallModules::class)
        );
        $this->container->getFileStorage()->save($listModules->dump(), UpgradeFileNames::MODULES_TO_DOWNLOAD_LIST);

        if ($modulesLeft) {
            $this->stepDone = false;
            $this->next = TaskName::TASK_DOWNLOAD_MODULES;
            $this->logger->info($this->translator->trans('%s modules left to check.', [$modulesLeft]));
        } else {
            $this->stepDone = true;
            $this->status = 'ok';
            $this->next = TaskName::TASK_UNINSTALL_MODULES;
            $this->logger->info($this->translator->trans('All modules have been downloaded.'));
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

    /**
     * @throws Exception
     */
    private function warmUp(): int
    {
        $this->container->getUpdateState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        try {
            $modulesToDownload = $this->container->getModuleAdapter()->listModulesPresentInFolderAndInstalled();
            $modulesToDownload = array_reverse($modulesToDownload);
            $totalModulesToDownload = count($modulesToDownload);

            $this->container->getFileStorage()->save(
                (new Backlog($modulesToDownload, $totalModulesToDownload))->dump(),
                UpgradeFileNames::MODULES_TO_DOWNLOAD_LIST
            );

            $this->container->getFileStorage()->save(
                (new Backlog([], 0))->dump(),
                UpgradeFileNames::MODULES_TO_UPGRADE_LIST
            );
        } catch (ProcessException $e) {
            $this->handleException($e);

            return ExitCode::FAIL;
        }

        if ($totalModulesToDownload) {
            $this->logger->info($this->translator->trans('%s installed modules will be checked.', [$totalModulesToDownload]));
        }

        $this->stepDone = false;
        $this->next = TaskName::TASK_DOWNLOAD_MODULES;

        return ExitCode::SUCCESS;
    }
}
