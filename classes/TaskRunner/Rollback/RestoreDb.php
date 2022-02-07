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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Rollback;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Database;

/**
 * Restores database from backup file.
 */
class RestoreDb extends AbstractTask
{
    public function run()
    {
        $databaseTools = new Database($this->container->getDb());
        $ignore_stats_table = [
            _DB_PREFIX_ . 'connections',
            _DB_PREFIX_ . 'connections_page',
            _DB_PREFIX_ . 'connections_source',
            _DB_PREFIX_ . 'guest',
            _DB_PREFIX_ . 'statssearch',
        ];
        $startTime = time();
        $listQuery = [];

        // deal with running backup rest if exist
        if (file_exists($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::QUERIES_TO_RESTORE_LIST)) {
            $listQuery = $this->container->getFileConfigurationStorage()->load(UpgradeFileNames::QUERIES_TO_RESTORE_LIST);
        }

        // deal with the next files stored in restoreDbFilenames
        $restoreDbFilenames = $this->container->getState()->getRestoreDbFilenames();
        if (empty($listQuery) && count($restoreDbFilenames) > 0) {
            $currentDbFilename = array_shift($restoreDbFilenames);
            $this->container->getState()->setRestoreDbFilenames($restoreDbFilenames);
            if (!preg_match('#auto-backupdb_([0-9]{6})_#', $currentDbFilename, $match)) {
                $this->next = 'error';
                $this->error = true;
                $this->logger->error($this->translator->trans('%s: File format does not match.', [$currentDbFilename], 'Modules.Autoupgrade.Admin'));

                return false;
            }
            $this->container->getState()->setDbStep($match[1]);
            $backupdb_path = $this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $this->container->getState()->getRestoreName();

            $dot_pos = strrpos($currentDbFilename, '.');
            $fileext = substr($currentDbFilename, $dot_pos + 1);
            $requests = [];
            $content = '';

            $this->logger->debug($this->translator->trans(
                'Opening backup database file %filename% in %extension% mode',
                [
                    '%filename%' => $currentDbFilename,
                    '%extension%' => $fileext,
                ],
                'Modules.Autoupgrade.Admin'
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
            $currentDbFilename = '';

            if (empty($content)) {
                $this->logger->error($this->translator->trans('Database backup is empty.', [], 'Modules.Autoupgrade.Admin'));
                $this->next = 'rollback';

                return false;
            }

            // preg_match_all is better than preg_split (what is used in do Upgrade.php)
            // This way we avoid extra blank lines
            // option s (PCRE_DOTALL) added
            $listQuery = preg_split('/;[\n\r]+/Usm', $content);
            unset($content);

            // Get tables before backup
            if ($this->container->getState()->getDbStep() == '1') {
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
                    $this->container->getFileConfigurationStorage()->save($tablesToRemove, UpgradeFileNames::DB_TABLES_TO_CLEAN_LIST);
                }
            }
        }

        /** @todo : error if listQuery is not an array (that can happen if toRestoreQueryList is empty for example) */
        $time_elapsed = time() - $startTime;
        if (is_array($listQuery) && count($listQuery) > 0) {
            $this->container->getDb()->execute('SET SESSION sql_mode = \'\'');
            $this->container->getDb()->execute('SET FOREIGN_KEY_CHECKS=0');

            do {
                if (count($listQuery) == 0) {
                    if (file_exists($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::QUERIES_TO_RESTORE_LIST)) {
                        unlink($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::QUERIES_TO_RESTORE_LIST);
                    }

                    $restoreDbFilenamesCount = count($this->container->getState()->getRestoreDbFilenames());
                    if ($restoreDbFilenamesCount) {
                        $this->logger->info($this->translator->trans(
                            'Database restoration file %filename% done. %filescount% file(s) left...',
                            [
                                '%filename%' => $this->container->getState()->getDbStep(),
                                '%filescount%' => $restoreDbFilenamesCount,
                            ],
                            'Modules.Autoupgrade.Admin'
                        ));
                    } else {
                        $this->logger->info($this->translator->trans('Database restoration file %1$s done.', [$this->container->getState()->getDbStep()], 'Modules.Autoupgrade.Admin'));
                    }

                    $this->stepDone = true;
                    $this->status = 'ok';
                    $this->next = 'restoreDb';

                    if ($restoreDbFilenamesCount === 0) {
                        $this->next = 'rollbackComplete';
                        $this->logger->info($this->translator->trans('Database has been restored.', [], 'Modules.Autoupgrade.Admin'));

                        $databaseTools->cleanTablesAfterBackup($this->container->getFileConfigurationStorage()->load(UpgradeFileNames::DB_TABLES_TO_CLEAN_LIST));
                        $this->container->getFileConfigurationStorage()->clean(UpgradeFileNames::DB_TABLES_TO_CLEAN_LIST);
                    }

                    return true;
                }

                // filesForBackup already contains all the correct files
                if (count($listQuery) == 0) {
                    continue;
                }

                $query = trim(array_shift($listQuery));
                if (!empty($query)) {
                    if (!$this->container->getDb()->execute($query, false)) {
                        if (is_array($listQuery)) {
                            $listQuery = array_unshift($listQuery, $query);
                        }
                        $this->logger->error($this->translator->trans('[SQL ERROR]', [], 'Modules.Autoupgrade.Admin') . ' ' . $query . ' - ' . $this->container->getDb()->getMsgError());
                        $this->logger->info($this->translator->trans('Error during database restoration', [], 'Modules.Autoupgrade.Admin'));
                        $this->next = 'error';
                        $this->error = true;
                        unlink($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::QUERIES_TO_RESTORE_LIST);

                        return false;
                    }
                }

                // note : theses queries can be too big and can cause issues for display
                // else
                // $this->nextQuickInfo[] = '[OK] '.$query;

                $time_elapsed = time() - $startTime;
            } while ($time_elapsed < $this->container->getUpgradeConfiguration()->getTimePerCall());

            $queries_left = count($listQuery);

            if ($queries_left > 0) {
                $this->container->getFileConfigurationStorage()->save($listQuery, UpgradeFileNames::QUERIES_TO_RESTORE_LIST);
            } elseif (file_exists($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::QUERIES_TO_RESTORE_LIST)) {
                unlink($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::QUERIES_TO_RESTORE_LIST);
            }

            $this->stepDone = false;
            $this->next = 'restoreDb';
            $this->logger->info($this->translator->trans(
                '%numberqueries% queries left for file %filename%...',
                [
                    '%numberqueries%' => $queries_left,
                    '%filename%' => $this->container->getState()->getDbStep(),
                ],
                'Modules.Autoupgrade.Admin'
            ));
            unset($query, $listQuery);
        } else {
            $this->stepDone = true;
            $this->status = 'ok';
            $this->next = 'rollbackComplete';
            $this->logger->info($this->translator->trans('Database restoration done.', [], 'Modules.Autoupgrade.Admin'));

            $databaseTools->cleanTablesAfterBackup($this->container->getFileConfigurationStorage()->load(UpgradeFileNames::DB_TABLES_TO_CLEAN_LIST));
            $this->container->getFileConfigurationStorage()->clean(UpgradeFileNames::DB_TABLES_TO_CLEAN_LIST);
        }

        return true;
    }

    public function init()
    {
        // We don't need the whole core being instanciated, only the autoloader
        $this->container->initPrestaShopAutoloader();

        // Loads the parameters.php file on PrestaShop 1.7, needed for accessing the database
        if (file_exists($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/config/bootstrap.php')) {
            require_once $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . '/config/bootstrap.php';
        }
    }
}
