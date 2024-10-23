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
            if (!$this->container->getFileConfigurationStorage()->exists(UpgradeFileNames::SQL_TO_EXECUTE_LIST)) {
                $this->warmUp();
                $originVersion = $this->container->getState()->getOriginVersion();
                $sqlContentList = $this->getCoreUpgrader()->getSqlContentList($originVersion);
                $backlog = new Backlog(array_reverse($sqlContentList), count($sqlContentList));
            } else {
                $this->getCoreUpgrader()->setupUpdateEnvironment();
                $backlog = Backlog::fromContents($this->container->getFileConfigurationStorage()->load(UpgradeFileNames::SQL_TO_EXECUTE_LIST));
            }

            if ($backlog->getRemainingTotal() > 0) {
                $this->logger->info($this->translator->trans('Update database in progress. %d queries left', [$backlog->getRemainingTotal()]));

                $this->updateDatabase($backlog);

                $this->container->getState()->setProgressPercentage(
                    $this->container->getCompletionCalculator()->computePercentage($backlog, self::class, UpdateModules::class)
                );

                $this->next = TaskName::TASK_UPDATE_DATABASE;
                $this->stepDone = false;

                return ExitCode::SUCCESS;
            }
            $this->container->getFileConfigurationStorage()->clean(UpgradeFileNames::SQL_TO_EXECUTE_LIST);
            $this->getCoreUpgrader()->finalizeCoreUpdate();
        } catch (UpgradeException $e) {
            $this->next = TaskName::TASK_ERROR;
            $this->setErrorFlag();
            foreach ($e->getQuickInfos() as $log) {
                $this->logger->debug($log);
            }
            $this->logger->error($this->translator->trans('Error during database upgrade. You may need to restore your database.'));
            $this->logger->error($e->getMessage());

            return ExitCode::FAIL;
        }
        $this->next = TaskName::TASK_UPDATE_MODULES;
        $this->stepDone = true;
        $this->logger->info($this->translator->trans('Database upgraded. Now upgrading your Addons modules...'));

        return ExitCode::SUCCESS;
    }

    public function getCoreUpgrader(): CoreUpgrader
    {
        if ($this->coreUpgrader !== null) {
            return $this->coreUpgrader;
        }

        if (version_compare($this->container->getState()->getInstallVersion(), '8', '<')) {
            $this->coreUpgrader = new CoreUpgrader17($this->container, $this->logger);
        } elseif (version_compare($this->container->getState()->getInstallVersion(), '8.1', '<')) {
            $this->coreUpgrader = new CoreUpgrader80($this->container, $this->logger);
        } else {
            $this->coreUpgrader = new CoreUpgrader81($this->container, $this->logger);
        }

        return $this->coreUpgrader;
    }

    public function init(): void
    {
        if (!$this->container->getFileConfigurationStorage()->exists(UpgradeFileNames::SQL_TO_EXECUTE_LIST)) {
            $this->logger->info($this->translator->trans('Cleaning file cache'));
            $this->container->getCacheCleaner()->cleanFolders();
            $this->logger->info($this->translator->trans('Running opcache_reset'));
            $this->container->resetOpcache();
        }

        // Migrating settings file
        $this->container->initPrestaShopAutoloader();
        parent::init();
    }

    /**
     * @throws UpgradeException
     * @throws Exception
     */
    protected function warmUp(): int
    {
        $this->logger->info($this->container->getTranslator()->trans('Updating database data and structure'));

        $this->container->getState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $this->getCoreUpgrader()->writeNewSettings();

        $this->logger->info($this->container->getTranslator()->trans('Checking version validity'));
        $this->checkVersionIsNewer();

        $this->getCoreUpgrader()->setupUpdateEnvironment();

        if ($this->container->getUpgradeConfiguration()->shouldDeactivateCustomModules()) {
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
        $originVersion = VersionUtils::normalizePrestaShopVersion($this->container->getState()->getOriginVersion());
        $installVersion = VersionUtils::normalizePrestaShopVersion($this->container->getState()->getInstallVersion());

        $versionCompare = version_compare($installVersion, $originVersion);

        if ($versionCompare === -1) {
            throw new UpgradeException($this->container->getTranslator()->trans('[ERROR] Version to install is too old.') . ' ' . $this->container->getTranslator()->trans('Current version: %oldversion%. Version to install: %newversion%.', ['%oldversion%' => $originVersion, '%newversion%' => $installVersion]));
        } elseif ($versionCompare === 0) {
            throw new UpgradeException($this->container->getTranslator()->trans('You already have the %s version.', [$installVersion]));
        }
    }

    protected function updateDatabase(Backlog $backlog): void
    {
        $sqlContent = $backlog->getNext();
        $this->getCoreUpgrader()->runQuery($sqlContent['version'], $sqlContent['query']);
        $this->container->getFileConfigurationStorage()->save($backlog->dump(), UpgradeFileNames::SQL_TO_EXECUTE_LIST);
    }
}
