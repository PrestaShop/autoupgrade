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
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Progress\Backlog;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader17;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader80;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader81;
use PrestaShop\Module\AutoUpgrade\VersionUtils;

class UpdateDatabase extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_UPDATE;

    /** @var CoreUpgrader */
    private $coreUpgrader;

    public function run(): int
    {
        try {
            if (!$this->container->getFileStorage()->exists(UpgradeFileNames::SQL_TO_EXECUTE_LIST)) {
                $this->warmUp();
                $currentVersion = $this->container->getUpdateState()->getCurrentVersion();
                $sqlContentList = $this->getCoreUpgrader()->getSqlContentList($currentVersion);
                $backlog = new Backlog(array_reverse($sqlContentList), count($sqlContentList));
            } else {
                $this->getCoreUpgrader()->setupUpdateEnvironment();
                $backlog = Backlog::fromContents($this->container->getFileStorage()->load(UpgradeFileNames::SQL_TO_EXECUTE_LIST));
            }

            if ($backlog->getRemainingTotal() > 0) {
                $this->logger->info($this->translator->trans('Update database in progress. %d queries left', [$backlog->getRemainingTotal()]));

                $this->updateDatabase($backlog);

                $this->container->getUpdateState()->setProgressPercentage(
                    $this->container->getCompletionCalculator()->computePercentage($backlog, self::class, UpdateModules::class)
                );

                $this->next = TaskName::TASK_UPDATE_DATABASE;
                $this->stepDone = false;

                return ExitCode::SUCCESS;
            }
            $this->container->getFileStorage()->clean(UpgradeFileNames::SQL_TO_EXECUTE_LIST);
            $this->getCoreUpgrader()->finalizeCoreUpdate();
        } catch (UpgradeException $e) {
            $this->next = TaskName::TASK_ERROR;
            $this->setErrorFlag();
            foreach ($e->getQuickInfos() as $log) {
                $this->logger->debug($log);
            }
            $this->logger->error($this->translator->trans('Error during database update. You may need to restore your database.'));
            $this->logger->error($e->getMessage());

            return ExitCode::FAIL;
        }
        $this->next = TaskName::TASK_UPDATE_MODULES;
        $this->stepDone = true;
        $this->logger->info($this->translator->trans('Database updated. Now updating your Addons modules...'));

        return ExitCode::SUCCESS;
    }

    public function getCoreUpgrader(): CoreUpgrader
    {
        if ($this->coreUpgrader !== null) {
            return $this->coreUpgrader;
        }

        if (version_compare($this->container->getUpdateState()->getDestinationVersion(), '8', '<')) {
            $this->coreUpgrader = new CoreUpgrader17($this->container, $this->logger);
        } elseif (version_compare($this->container->getUpdateState()->getDestinationVersion(), '8.1', '<')) {
            $this->coreUpgrader = new CoreUpgrader80($this->container, $this->logger);
        } else {
            $this->coreUpgrader = new CoreUpgrader81($this->container, $this->logger);
        }

        return $this->coreUpgrader;
    }

    public function init(): void
    {
        if (!$this->container->getFileStorage()->exists(UpgradeFileNames::SQL_TO_EXECUTE_LIST)) {
            $this->logger->info($this->translator->trans('Cleaning file cache'));
            $this->container->getCacheCleaner()->cleanFolders();
        }

        // Migrating settings file
        $this->container->initPrestaShopAutoloader();
        $this->container->initPrestaShopCore();
    }

    /**
     * @throws UpgradeException
     * @throws Exception
     */
    protected function warmUp(): int
    {
        $this->logger->info($this->container->getTranslator()->trans('Updating database data and structure'));

        $this->container->getUpdateState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $this->getCoreUpgrader()->writeNewSettings();

        $this->logger->info($this->container->getTranslator()->trans('Checking version validity'));
        $this->checkVersionIsNewer();

        if ($this->getCoreUpgrader()->shouldWarmupCoreCache()) {
            $this->getCoreUpgrader()->warmupCoreCache();
        }

        $this->getCoreUpgrader()->setupUpdateEnvironment();

        if ($this->container->getUpdateConfiguration()->shouldDeactivateCustomModules()) {
            $this->logger->info($this->container->getTranslator()->trans('Disabling all non native modules'));
            $this->getCoreUpgrader()->disableCustomModules();
        } else {
            $this->logger->info($this->container->getTranslator()->trans('Keeping non native modules enabled'));
        }

        return ExitCode::SUCCESS;
    }

    /**
     * @throws UpgradeException
     */
    protected function checkVersionIsNewer(): void
    {
        $currentVersion = VersionUtils::normalizePrestaShopVersion($this->container->getUpdateState()->getCurrentVersion());
        $destinationVersion = VersionUtils::normalizePrestaShopVersion($this->container->getUpdateState()->getDestinationVersion());

        $versionCompare = version_compare($destinationVersion, $currentVersion);

        if ($versionCompare === -1) {
            throw new UpgradeException($this->container->getTranslator()->trans('Version to install is too old. Current version: %oldversion%. Version to install: %newversion%.', ['%oldversion%' => $currentVersion, '%newversion%' => $destinationVersion]));
        } elseif ($versionCompare === 0) {
            throw new UpgradeException($this->container->getTranslator()->trans('You already have the %s version.', [$destinationVersion]));
        }
    }

    protected function updateDatabase(Backlog $backlog): void
    {
        $sqlContent = $backlog->getNext();
        $this->getCoreUpgrader()->runQuery($sqlContent['version'], $sqlContent['query']);
        $this->container->getFileStorage()->save($backlog->dump(), UpgradeFileNames::SQL_TO_EXECUTE_LIST);
    }
}
