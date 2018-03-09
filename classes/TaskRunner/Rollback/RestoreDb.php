<?php
/*
 * 2007-2018 PrestaShop
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * 
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Rollback;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Database;

/**
 * Restores database from backup file
 */
class RestoreDb extends AbstractTask
{
    public function run()
    {
        $databaseTools = new Database($this->upgradeClass->db);
        $ignore_stats_table = array(
            _DB_PREFIX_.'connections',
            _DB_PREFIX_.'connections_page',
            _DB_PREFIX_.'connections_source',
            _DB_PREFIX_.'guest',
            _DB_PREFIX_.'statssearch'
        );
        $this->upgradeClass->nextParams['dbStep'] = $this->upgradeClass->currentParams['dbStep'];
        $start_time = time();
        $listQuery = array();

        // deal with running backup rest if exist
        if (file_exists($this->upgradeClass->autoupgradePath.DIRECTORY_SEPARATOR.UpgradeFileNames::toRestoreQueryList)) {
            $listQuery = $this->upgradeClass->getFileConfigurationStorage()->load(UpgradeFileNames::toRestoreQueryList);
        }

        // deal with the next files stored in restoreDbFilenames
        $restoreDbFilenames = $this->upgradeClass->getState()-> getRestoreDbFilenames();
        if (empty($listQuery) && count($restoreDbFilenames) > 0) {
            $currentDbFilename = array_shift($restoreDbFilenames);
            $this->upgradeClass->getState()-> setRestoreDbFilenames($restoreDbFilenames);
            if (!preg_match('#auto-backupdb_([0-9]{6})_#', $currentDbFilename, $match)) {
                $this->upgradeClass->next = 'error';
                $this->upgradeClass->error = 1;
                $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('%s: File format does not match.', array($currentDbFilename), 'Modules.Autoupgrade.Admin');
                return false;
            }
            $this->upgradeClass->nextParams['dbStep'] = $match[1];
            $backupdb_path = $this->upgradeClass->backupPath.DIRECTORY_SEPARATOR.$this->upgradeClass->getState()-> getRestoreName();

            $dot_pos = strrpos($currentDbFilename, '.');
            $fileext = substr($currentDbFilename, $dot_pos+1);
            $requests = array();
            $content = '';

            $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans(
                'Opening backup database file %filename% in %extension% mode',
                array(
                    '%filename%' => $currentDbFilename,
                    '%extension%' => $fileext,
                ),
                'Modules.Autoupgrade.Admin'
            );

            switch ($fileext) {
                case 'bz':
                case 'bz2':
                    if ($fp = bzopen($backupdb_path.DIRECTORY_SEPARATOR.$currentDbFilename, 'r')) {
                        while (!feof($fp)) {
                            $content .= bzread($fp, 4096);
                        }
                    } else {
                        die("error when trying to open in bzmode");
                    } // @todo : handle error
                    break;
                case 'gz':
                    if ($fp = gzopen($backupdb_path.DIRECTORY_SEPARATOR.$currentDbFilename, 'r')) {
                        while (!feof($fp)) {
                            $content .= gzread($fp, 4096);
                        }
                    }
                    gzclose($fp);
                    break;
                default:
                    if ($fp = fopen($backupdb_path.DIRECTORY_SEPARATOR.$currentDbFilename, 'r')) {
                        while (!feof($fp)) {
                            $content .= fread($fp, 4096);
                        }
                    }
                    fclose($fp);
            }
            $currentDbFilename = '';

            if (empty($content)) {
                $this->upgradeClass->nextErrors[] =
                $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('Database backup is empty.', array(), 'Modules.Autoupgrade.Admin');
                $this->upgradeClass->next = 'rollback';
                return false;
            }

            // preg_match_all is better than preg_split (what is used in do Upgrade.php)
            // This way we avoid extra blank lines
            // option s (PCRE_DOTALL) added
            $listQuery = preg_split('/;[\n\r]+/Usm', $content);
            unset($content);

            // Get tables before backup
            if ($this->upgradeClass->nextParams['dbStep'] == '1') {
                $tables_after_restore = array();
                foreach ($listQuery as $q) {
                    if (preg_match('/`(?<table>'._DB_PREFIX_.'[a-zA-Z0-9_-]+)`/', $q, $matches)) {
                        if (isset($matches['table'])) {
                            $tables_after_restore[$matches['table']] = $matches['table'];
                        }
                    }
                }

                $tables_after_restore = array_unique($tables_after_restore);
                $tables_before_restore = $databaseTools->getAllTables();
                $tablesToRemove = array_diff($tables_before_restore, $tables_after_restore);

                if (!empty($tablesToRemove)) {
                    $this->upgradeClass->getFileConfigurationStorage()->save($tablesToRemove, UpgradeFileNames::toCleanTable);
                }
            }
        }

        // @todo : error if listQuery is not an array (that can happen if toRestoreQueryList is empty for example)
        $time_elapsed = time() - $start_time;
        if (is_array($listQuery) && count($listQuery) > 0) {
            $this->upgradeClass->db->execute('SET SESSION sql_mode = \'\'');
            $this->upgradeClass->db->execute('SET FOREIGN_KEY_CHECKS=0');

            do {
                if (count($listQuery) == 0) {
                    if (file_exists($this->upgradeClass->autoupgradePath.DIRECTORY_SEPARATOR.UpgradeFileNames::toRestoreQueryList)) {
                        unlink($this->upgradeClass->autoupgradePath.DIRECTORY_SEPARATOR.UpgradeFileNames::toRestoreQueryList);
                    }

                    $restoreDbFilenamesCount = count($this->upgradeClass->getState()-> getRestoreDbFilenames());
                    if ($restoreDbFilenamesCount) {
                        $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans(
                            'Database restoration file %filename% done. %filescount% file(s) left...',
                            array(
                                '%filename%' => $this->upgradeClass->nextParams['dbStep'],
                                '%filescount%' => $restoreDbFilenamesCount,
                            ),
                            'Modules.Autoupgrade.Admin'
                        );
                    } else {
                        $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('Database restoration file %1$s done.', array($this->upgradeClass->nextParams['dbStep']), 'Modules.Autoupgrade.Admin');
                    }

                    $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->next_desc;
                    $this->upgradeClass->stepDone = true;
                    $this->upgradeClass->status = 'ok';
                    $this->upgradeClass->next = 'restoreDb';

                    if ($restoreDbFilenamesCount === 0) {
                        $this->upgradeClass->next = 'rollbackComplete';
                        $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('Database has been restored.', array(), 'Modules.Autoupgrade.Admin');

                        $databaseTools->cleanTablesAfterBackup($this->upgradeClass->getFileConfigurationStorage()->load(UpgradeFileNames::toCleanTable));
                        $this->upgradeClass->getFileConfigurationStorage()->clean(UpgradeFileNames::toCleanTable);
                    }
                    return true;
                }

                // filesForBackup already contains all the correct files
                if (count($listQuery) == 0) {
                    continue;
                }

                $query = trim(array_shift($listQuery));
                if (!empty($query)) {
                    if (!$this->upgradeClass->db->execute($query, false)) {
                        if (is_array($listQuery)) {
                            $listQuery = array_unshift($listQuery, $query);
                        }
                        $this->upgradeClass->nextErrors[] =
                        $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('[SQL ERROR]', array(), 'Modules.Autoupgrade.Admin').' '.$query.' - '.$this->upgradeClass->db->getMsgError();
                        $this->upgradeClass->next = 'error';
                        $this->upgradeClass->error = 1;
                        $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('Error during database restoration', array(), 'Modules.Autoupgrade.Admin');
                        unlink($this->upgradeClass->autoupgradePath.DIRECTORY_SEPARATOR.UpgradeFileNames::toRestoreQueryList);
                        return false;
                    }
                }

                // note : theses queries can be too big and can cause issues for display
                // else
                // $this->upgradeClass->nextQuickInfo[] = '[OK] '.$query;

                $time_elapsed = time() - $start_time;
            } while ($time_elapsed < \AdminSelfUpgrade::$loopRestoreQueryTime);

            $queries_left = count($listQuery);

            if ($queries_left > 0) {
                $this->upgradeClass->getFileConfigurationStorage()->save($listQuery, UpgradeFileNames::toRestoreQueryList);
            } elseif (file_exists($this->upgradeClass->autoupgradePath.DIRECTORY_SEPARATOR.UpgradeFileNames::toRestoreQueryList)) {
                unlink($this->upgradeClass->autoupgradePath.DIRECTORY_SEPARATOR.UpgradeFileNames::toRestoreQueryList);
            }

            $this->upgradeClass->stepDone = false;
            $this->upgradeClass->next = 'restoreDb';
            $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans(
                '%numberqueries% queries left for file %filename%...',
                array(
                    '%numberqueries%' => $queries_left,
                    '%filename%' => $this->upgradeClass->nextParams['dbStep'],
                ),
                'Modules.Autoupgrade.Admin'
            );
            unset($query, $listQuery);
        } else {
            $this->upgradeClass->stepDone = true;
            $this->upgradeClass->status = 'ok';
            $this->upgradeClass->next = 'rollbackComplete';
            $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('Database restoration done.', array(), 'Modules.Autoupgrade.Admin');

            $databaseTools->cleanTablesAfterBackup($this->upgradeClass->getFileConfigurationStorage()->load(UpgradeFileNames::toCleanTable));
            $this->upgradeClass->getFileConfigurationStorage()->clean(UpgradeFileNames::toCleanTable);
        }
        return true;
    }
}