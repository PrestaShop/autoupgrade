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

namespace PrestaShop\Module\AutoUpgrade\Task\Restore;

use Exception;
use PrestaShop\Module\AutoUpgrade\Backup\BackupFinder;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Progress\Backlog;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Database;

/**
 * Restores database from backup file.
 */
class RestoreDatabase extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_RESTORE;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        $state = $this->container->getRestoreState();
        $state->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $databaseTools = new Database($this->container->getDb());
        $ignore_stats_table = [
            _DB_PREFIX_ . 'connections',
            _DB_PREFIX_ . 'connections_page',
            _DB_PREFIX_ . 'connections_source',
            _DB_PREFIX_ . 'guest',
            _DB_PREFIX_ . 'statssearch',
        ];
        $startTime = time();
        $queriesToRestoreListPath = $this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::QUERIES_TO_RESTORE_LIST;

        // deal with running backup rest if exist
        if ($this->container->getFileSystem()->exists($queriesToRestoreListPath)) {
            $backlog = Backlog::fromContents($this->container->getFileStorage()->load(UpgradeFileNames::QUERIES_TO_RESTORE_LIST));
        }

        // deal with the next files stored in restoreDbFilenames
        $restoreDbFilenames = $state->getRestoreDbFilenames();
        if ((!isset($backlog) || !$backlog->getRemainingTotal()) && count($restoreDbFilenames) > 0) {
            $currentDbFilename = array_shift($restoreDbFilenames);
            $state->setRestoreDbFilenames($restoreDbFilenames);
            if (!preg_match('#' . BackupFinder::BACKUP_DB_FOLDER_NAME_PREFIX . '([0-9]{6})_#', $currentDbFilename, $match)) {
                $this->next = TaskName::TASK_ERROR;
                $this->setErrorFlag();
                $this->logger->error($this->translator->trans('%s: File format does not match.', [$currentDbFilename]));

                return ExitCode::FAIL;
            }
            $state->setDbStep((int) $match[1]);
            $backupdb_path = $this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $state->getRestoreName();

            $dot_pos = strrpos($currentDbFilename, '.');
            $fileext = substr($currentDbFilename, $dot_pos + 1);
            $content = '';

            $this->logger->debug($this->translator->trans(
                'Opening backup database file %filename% in %extension% mode',
                [
                    '%filename%' => $currentDbFilename,
                    '%extension%' => $fileext,
                ]
            ));

            switch ($fileext) {
                case 'bz':
                case 'bz2':
                    $fp = bzopen($backupdb_path . DIRECTORY_SEPARATOR . $currentDbFilename, 'r');
                    if (is_resource($fp)) {
                        while (!feof($fp)) {
                            $content .= bzread($fp, 4096);
                        }
                        bzclose($fp);
                    }
                    break;
                case 'gz':
                    $fp = gzopen($backupdb_path . DIRECTORY_SEPARATOR . $currentDbFilename, 'r');
                    if (is_resource($fp)) {
                        while (!feof($fp)) {
                            $content .= gzread($fp, 4096);
                        }
                        gzclose($fp);
                    }
                    break;
                default:
                    $fp = fopen($backupdb_path . DIRECTORY_SEPARATOR . $currentDbFilename, 'r');
                    if (is_resource($fp)) {
                        while (!feof($fp)) {
                            $content .= fread($fp, 4096);
                        }
                        fclose($fp);
                    }
            }

            if (empty($content)) {
                $this->logger->error($this->translator->trans('Database backup is empty.'));
                $this->next = TaskName::TASK_RESTORE_INITIALIZATION;

                return ExitCode::FAIL;
            }

            // preg_match_all is better than preg_split (what is used in do Upgrade.php)
            // This way we avoid extra blank lines
            // option s (PCRE_DOTALL) added
            $listQuery = preg_split('/;[\n\r]+/Usm', $content);
            unset($content);

            // Get tables before backup
            if ($state->getDbStep() == '1') {
                $tables_after_restore = [];
                foreach ($listQuery as $q) {
                    if (preg_match('/`(?<table>' . _DB_PREFIX_ . '[a-zA-Z0-9_-]+)`/', $q, $matches)) {
                        if (isset($matches['table'])) {
                            $tables_after_restore[$matches['table']] = $matches['table'];
                        }
                    }
                }

                $tables_after_restore = array_unique($tables_after_restore);
                $tables_before_restore = $databaseTools->getAllTables();
                $tablesToRemove = array_diff($tables_before_restore, $tables_after_restore, $ignore_stats_table);

                if (!empty($tablesToRemove)) {
                    $this->container->getFileStorage()->save($tablesToRemove, UpgradeFileNames::DB_TABLES_TO_CLEAN_LIST);
                    // TODO: This is a temporary workaround and does not properly handle.
                    $databaseTools->cleanTablesAfterBackup($this->container->getFileStorage()->load(UpgradeFileNames::DB_TABLES_TO_CLEAN_LIST));
                }
            }
            $backlog = new Backlog(array_reverse($listQuery), count($listQuery));
        }

        /* @todo : error if listQuery is not an array (that can happen if toRestoreQueryList is empty for example) */
        if (isset($backlog) && $backlog->getRemainingTotal()) {
            $this->container->getDb()->execute('SET SESSION sql_mode = \'\'');
            $this->container->getDb()->execute('SET FOREIGN_KEY_CHECKS=0');

            do {
                // @phpstan-ignore booleanNot.alwaysFalse (Need a refacto of this whole task)
                if (!$backlog->getRemainingTotal()) {
                    if ($this->container->getFileSystem()->exists($queriesToRestoreListPath)) {
                        $this->container->getFileSystem()->remove($queriesToRestoreListPath);
                    }

                    $restoreDbFilenamesCount = count($state->getRestoreDbFilenames());
                    if ($restoreDbFilenamesCount) {
                        $this->logger->info($this->translator->trans(
                            'Database restoration file %filename% done. %filescount% file(s) left...',
                            [
                                '%filename%' => $state->getDbStep(),
                                '%filescount%' => $restoreDbFilenamesCount,
                            ]
                        ));
                    } else {
                        $this->logger->info($this->translator->trans('Database restoration file %1$s done.', [$state->getDbStep()]));
                    }

                    $this->stepDone = true;
                    $this->status = 'ok';
                    $this->next = TaskName::TASK_RESTORE_DATABASE;

                    if ($restoreDbFilenamesCount === 0) {
                        $this->next = TaskName::TASK_RESTORE_COMPLETE;
                        $this->logger->info($this->translator->trans('Database has been restored.'));

                        $databaseTools->cleanTablesAfterBackup($this->container->getFileStorage()->load(UpgradeFileNames::DB_TABLES_TO_CLEAN_LIST));
                        $this->container->getFileStorage()->clean(UpgradeFileNames::DB_TABLES_TO_CLEAN_LIST);
                    }

                    return ExitCode::SUCCESS;
                }

                $query = trim($backlog->getNext());
                if (!empty($query)) {
                    if (!$this->container->getDb()->execute($query, false)) {
                        $this->logger->error($this->translator->trans('Error during database restoration: ') . ' ' . $query . ' - ' . $this->container->getDb()->getMsgError());
                        $this->setErrorFlag();
                        $this->container->getFileSystem()->remove($queriesToRestoreListPath);

                        return ExitCode::FAIL;
                    }
                }

                $time_elapsed = time() - $startTime;
            } while ($time_elapsed < $this->container->getUpdateConfiguration()->getTimePerCall());

            $queries_left = $backlog->getRemainingTotal();

            if ($queries_left > 0) {
                $this->container->getFileStorage()->save($backlog->dump(), UpgradeFileNames::QUERIES_TO_RESTORE_LIST);
            } elseif ($this->container->getFileSystem()->exists($queriesToRestoreListPath)) {
                $this->container->getFileSystem()->remove($queriesToRestoreListPath);
            }

            $this->stepDone = false;
            $this->next = TaskName::TASK_RESTORE_DATABASE;
            $this->logger->info($this->translator->trans(
                '%numberqueries% queries left for file %filename%...',
                [
                    '%numberqueries%' => $queries_left,
                    '%filename%' => $state->getDbStep(),
                ]
            ));
        } else {
            $this->stepDone = true;
            $this->status = 'ok';
            $this->next = TaskName::TASK_RESTORE_COMPLETE;
            $this->logger->info($this->translator->trans('Database restoration done.'));
        }

        return ExitCode::SUCCESS;
    }

    public function init(): void
    {
        // We don't need the whole core being instanciated, only the autoloader
        $this->container->initPrestaShopAutoloader();

        // Loads the parameters.php file on PrestaShop 1.7, needed for accessing the database
        if ($this->container->getFileSystem()->exists($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/config/bootstrap.php')) {
            require_once $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/config/bootstrap.php';
        }
    }
}
