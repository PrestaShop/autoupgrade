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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade;

use PrestaShop\Module\AutoUpgrade\Tools14;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;

class BackupDb extends AbstractTask
{
    public function run()
    {
        if (!$this->upgradeClass->getUpgradeConfiguration()->get('PS_AUTOUP_BACKUP')) {
            $this->upgradeClass->stepDone = true;
            $this->upgradeClass->nextParams['dbStep'] = 0;
            $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('Database backup skipped. Now upgrading files...', array(), 'Modules.Autoupgrade.Admin');
            $this->upgradeClass->next = 'upgradeFiles';
            return true;
        }

        $relative_backup_path = str_replace(_PS_ROOT_DIR_, '', $this->upgradeClass->backupPath);
        $report = '';
        if (!\ConfigurationTest::test_dir($relative_backup_path, false, $report)) {
            $this->logger->error($this->upgradeClass->getTranslator()->trans('Backup directory is not writable (%path%).', array('%path%' => $this->upgradeClass->backupPath), 'Modules.Autoupgrade.Admin'));
            $this->upgradeClass->next = 'error';
            $this->upgradeClass->error = 1;
            return false;
        }

        $this->upgradeClass->stepDone = false;
        $this->upgradeClass->next = 'backupDb';
        $this->upgradeClass->nextParams = $this->upgradeClass->currentParams;
        $start_time = time();
        $time_elapsed = 0;

        $psBackupAll = true;
        $psBackupDropTable = true;
        $ignore_stats_table = array();
        if (!$psBackupAll) {
            $ignore_stats_table = array(_DB_PREFIX_.'connections',
                                                        _DB_PREFIX_.'connections_page',
                                                        _DB_PREFIX_.'connections_source',
                                                        _DB_PREFIX_.'guest',
                                                        _DB_PREFIX_.'statssearch');
        }

        // INIT LOOP
        if (!$this->upgradeClass->getFileConfigurationStorage()->exists(UpgradeFileNames::toBackupDbList)) {
            if (!is_dir($this->upgradeClass->backupPath.DIRECTORY_SEPARATOR.$this->upgradeClass->getState()-> getBackupName())) {
                mkdir($this->upgradeClass->backupPath.DIRECTORY_SEPARATOR.$this->upgradeClass->getState()-> getBackupName());
            }
            $this->upgradeClass->nextParams['dbStep'] = 0;
            $tablesToBackup = $this->upgradeClass->db->executeS('SHOW TABLES LIKE "'._DB_PREFIX_.'%"', true, false);
            $this->upgradeClass->getFileConfigurationStorage()->save($tablesToBackup, UpgradeFileNames::toBackupDbList);
        }

        if (!isset($tablesToBackup)) {
            $tablesToBackup = $this->upgradeClass->getFileConfigurationStorage()->load(UpgradeFileNames::toBackupDbList);
        }
        $found = 0;
        $views = '';

        // MAIN BACKUP LOOP //
        $written = 0;
        do {
            if (!empty($this->upgradeClass->nextParams['backup_table'])) {
                // only insert (schema already done)
                $table = $this->upgradeClass->nextParams['backup_table'];
                $lines = $this->upgradeClass->nextParams['backup_lines'];
            } else {
                if (count($tablesToBackup) == 0) {
                    break;
                }
                $table = current(array_shift($tablesToBackup));
                $this->upgradeClass->nextParams['backup_loop_limit'] = 0;
            }

            if ($written == 0 || $written > \AdminSelfUpgrade::$max_written_allowed) {
                // increment dbStep will increment filename each time here
                $this->upgradeClass->nextParams['dbStep']++;
                // new file, new step
                $written = 0;
                if (isset($fp)) {
                    fclose($fp);
                }
                $backupfile = $this->upgradeClass->backupPath.DIRECTORY_SEPARATOR.$this->upgradeClass->getState()-> getBackupName().DIRECTORY_SEPARATOR.$this->upgradeClass->getState()-> getBackupDbFilename();
                $backupfile = preg_replace("#_XXXXXX_#", '_'.str_pad($this->upgradeClass->nextParams['dbStep'], 6, '0', STR_PAD_LEFT).'_', $backupfile);

                // start init file
                // Figure out what compression is available and open the file
                if (file_exists($backupfile)) {
                    $this->upgradeClass->next = 'error';
                    $this->upgradeClass->error = 1;
                    $this->logger->error($this->upgradeClass->getTranslator()->trans('Backup file %s already exists. Operation aborted.', array($backupfile), 'Modules.Autoupgrade.Admin'));
                }

                if (function_exists('bzopen')) {
                    $backupfile .= '.bz2';
                    $fp = bzopen($backupfile, 'w');
                } elseif (function_exists('gzopen')) {
                    $backupfile .= '.gz';
                    $fp = gzopen($backupfile, 'w');
                } else {
                    $fp = fopen($backupfile, 'w');
                }

                if ($fp === false) {
                    $this->logger->error($this->upgradeClass->getTranslator()->trans('Unable to create backup database file %s.', array(addslashes($backupfile)), 'Modules.Autoupgrade.Admin'));
                    $this->upgradeClass->next = 'error';
                    $this->upgradeClass->error = 1;
                    $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('Error during database backup.', array(), 'Modules.Autoupgrade.Admin');
                    return false;
                }

                $written += fwrite($fp, '/* Backup ' . $this->upgradeClass->nextParams['dbStep'] . ' for ' . Tools14::getHttpHost(false, false) . __PS_BASE_URI__ . "\n *  at " . date('r') . "\n */\n");
                $written += fwrite($fp, "\n".'SET SESSION sql_mode = \'\';'."\n\n");
                $written += fwrite($fp, "\n".'SET NAMES \'utf8\';'."\n\n");
                $written += fwrite($fp, "\n".'SET FOREIGN_KEY_CHECKS=0;'."\n\n");
                // end init file
            }


            // Skip tables which do not start with _DB_PREFIX_
            if (strlen($table) <= strlen(_DB_PREFIX_) || strncmp($table, _DB_PREFIX_, strlen(_DB_PREFIX_)) != 0) {
                continue;
            }

            // start schema : drop & create table only
            if (empty($this->upgradeClass->currentParams['backup_table'])) {
                // Export the table schema
                $schema = $this->upgradeClass->db->executeS('SHOW CREATE TABLE `' . $table . '`', true, false);

                if (count($schema) != 1 ||
                    !((isset($schema[0]['Table']) && isset($schema[0]['Create Table']))
                        || (isset($schema[0]['View']) && isset($schema[0]['Create View'])))) {
                    fclose($fp);
                    if (file_exists($backupfile)) {
                        unlink($backupfile);
                    }
                    $this->logger->error($this->upgradeClass->getTranslator()->trans('An error occurred while backing up. Unable to obtain the schema of %s', array($table), 'Modules.Autoupgrade.Admin'));
                    $this->logger->info($this->upgradeClass->getTranslator()->trans('Error during database backup.', array(), 'Modules.Autoupgrade.Admin'));
                    $this->upgradeClass->next = 'error';
                    $this->upgradeClass->error = 1;
                    return false;
                }

                // case view
                if (isset($schema[0]['View'])) {
                    $views .= '/* Scheme for view' . $schema[0]['View'] . " */\n";
                    if ($psBackupDropTable) {
                        // If some *upgrade* transform a table in a view, drop both just in case
                        $views .= 'DROP VIEW IF EXISTS `'.$schema[0]['View'].'`;'."\n";
                        $views .= 'DROP TABLE IF EXISTS `'.$schema[0]['View'].'`;'."\n";
                    }
                    $views .= preg_replace('#DEFINER=[^\s]+\s#', 'DEFINER=CURRENT_USER ', $schema[0]['Create View']).";\n\n";
                    $written += fwrite($fp, "\n".$views);
                    $ignore_stats_table[] = $schema[0]['View'];
                }
                // case table
                elseif (isset($schema[0]['Table'])) {
                    // Case common table
                    $written += fwrite($fp, '/* Scheme for table ' . $schema[0]['Table'] . " */\n");
                    if ($psBackupDropTable && !in_array($schema[0]['Table'], $ignore_stats_table)) {
                        // If some *upgrade* transform a table in a view, drop both just in case
                        $written += fwrite($fp, 'DROP VIEW IF EXISTS `'.$schema[0]['Table'].'`;'."\n");
                        $written += fwrite($fp, 'DROP TABLE IF EXISTS `'.$schema[0]['Table'].'`;'."\n");
                        // CREATE TABLE
                        $written += fwrite($fp, $schema[0]['Create Table'] . ";\n\n");
                    }
                    // schema created, now we need to create the missing vars
                    $this->upgradeClass->nextParams['backup_table'] = $table;
                    $lines = $this->upgradeClass->nextParams['backup_lines'] = explode("\n", $schema[0]['Create Table']);
                }
            }
            // end of schema

            // POPULATE TABLE
            if (!in_array($table, $ignore_stats_table)) {
                do {
                    $backup_loop_limit = $this->upgradeClass->nextParams['backup_loop_limit'];
                    $data = $this->upgradeClass->db->executeS('SELECT * FROM `'.$table.'` LIMIT '.(int)$backup_loop_limit.',200', false, false);
                    $this->upgradeClass->nextParams['backup_loop_limit'] += 200;
                    $sizeof = $this->upgradeClass->db->numRows();
                    if ($data && ($sizeof > 0)) {
                        // Export the table data
                        $written += fwrite($fp, 'INSERT INTO `'.$table."` VALUES\n");
                        $i = 1;
                        while ($row = $this->upgradeClass->db->nextRow($data)) {
                            // this starts a row
                            $s = '(';
                            foreach ($row as $field => $value) {
                                $tmp = "'" . $this->upgradeClass->db->escape($value, true) . "',";
                                if ($tmp != "'',") {
                                    $s .= $tmp;
                                } else {
                                    foreach ($lines as $line) {
                                        if (strpos($line, '`'.$field.'`') !== false) {
                                            if (preg_match('/(.*NOT NULL.*)/Ui', $line)) {
                                                $s .= "'',";
                                            } else {
                                                $s .= 'NULL,';
                                            }
                                            break;
                                        }
                                    }
                                }
                            }
                            $s = rtrim($s, ',');

                            if ($i < $sizeof) {
                                $s .= "),\n";
                            } else {
                                $s .= ");\n";
                            }

                            $written += fwrite($fp, $s);
                            ++$i;
                        }
                        $time_elapsed = time() - $start_time;
                    } else {
                        unset($this->upgradeClass->nextParams['backup_table']);
                        unset($this->upgradeClass->currentParams['backup_table']);
                        break;
                    }
                } while (($time_elapsed < \AdminSelfUpgrade::$loopBackupDbTime) && ($written < \AdminSelfUpgrade::$max_written_allowed));
            }
            $found++;
            $time_elapsed = time() - $start_time;
            $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('%s table has been saved.', array($table), 'Modules.Autoupgrade.Admin');
        } while (($time_elapsed < \AdminSelfUpgrade::$loopBackupDbTime) && ($written < \AdminSelfUpgrade::$max_written_allowed));

        // end of loop
        if (isset($fp)) {
            $written += fwrite($fp, "\n".'SET FOREIGN_KEY_CHECKS=1;'."\n\n");
            fclose($fp);
            unset($fp);
        }

        $this->upgradeClass->getFileConfigurationStorage()->save($tablesToBackup, UpgradeFileNames::toBackupDbList);

        if (count($tablesToBackup) > 0) {
            $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('%s tables have been saved.', array($found), 'Modules.Autoupgrade.Admin');
            $this->upgradeClass->next = 'backupDb';
            $this->upgradeClass->stepDone = false;
            if (count($tablesToBackup)) {
                $this->logger->info($this->upgradeClass->getTranslator()->trans('Database backup: %s table(s) left...', array(count($tablesToBackup)), 'Modules.Autoupgrade.Admin'));
            }
            return true;
        }
        if ($found == 0 && !empty($backupfile)) {
            if (file_exists($backupfile)) {
                unlink($backupfile);
            }
            $this->logger->error($this->upgradeClass->getTranslator()->trans('No valid tables were found to back up. Backup of file %s canceled.', array($backupfile), 'Modules.Autoupgrade.Admin'));
            $this->logger->info($this->upgradeClass->getTranslator()->trans('Error during database backup for file %s.', array($backupfile), 'Modules.Autoupgrade.Admin'));
            $this->upgradeClass->error = 1;
            return false;
        } else {
            unset($this->upgradeClass->nextParams['backup_loop_limit']);
            unset($this->upgradeClass->nextParams['backup_lines']);
            unset($this->upgradeClass->nextParams['backup_table']);
            if ($found) {
                $this->logger->info($this->upgradeClass->getTranslator()->trans('%s tables have been saved.', array($found), 'Modules.Autoupgrade.Admin'));
            }
            $this->upgradeClass->stepDone = true;
            // reset dbStep at the end of this step
            $this->upgradeClass->nextParams['dbStep'] = 0;

            $this->logger->info($this->upgradeClass->getTranslator()->trans('Database backup done in filename %s. Now upgrading files...', array($this->upgradeClass->getState()-> getBackupName()), 'Modules.Autoupgrade.Admin'));
            $this->upgradeClass->next = 'upgradeFiles';
            return true;
        }
    }
}