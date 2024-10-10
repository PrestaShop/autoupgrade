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
use PDO;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Progress\Backlog;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\Tools14;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class BackupDb extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_BACKUP;

    const MAX_SIZE_PER_INSERT_STMT = 950000;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        if (!$this->container->getUpgradeConfiguration()->shouldBackupFilesAndDatabase()) {
            $this->stepDone = true;
            $this->container->getState()->setDbStep(0);
            $this->logger->info($this->translator->trans('Database backup skipped. Now upgrading files...'));
            $this->next = 'upgradeFiles';

            return ExitCode::SUCCESS;
        }

        $this->stepDone = false;
        $this->next = 'backupDb';
        $start_time = time();
        $time_elapsed = 0;

        $db = $this->container->getDb();
        $dbLink = $db->connect();

        if (!$this->container->getFileConfigurationStorage()->exists(UpgradeFileNames::DB_TABLES_TO_BACKUP_LIST)) {
            return $this->warmUp();
        }

        $tablesToBackup = Backlog::fromContents($this->container->getFileConfigurationStorage()->load(UpgradeFileNames::DB_TABLES_TO_BACKUP_LIST));

        $numberOfSyncedTables = 0;
        $fp = false;
        $backupfile = null;

        // MAIN BACKUP LOOP //
        $written = 0;
        while ($this->isRemainingTimeEnough($time_elapsed)
            && $tablesToBackup->getRemainingTotal()
        ) {
            // Recover table partially synced
            $table = $this->container->getState()->getBackupTable();
            if (null === $table) {
                // Or get the next one to sync
                $table = $tablesToBackup->getNext();
                $this->container->getState()->setBackupLoopLimit(0);
            }

            if ($written > $this->container->getUpgradeConfiguration()->getMaxSizeToWritePerCall()) {
                // In the previous loop execution, we reached the limit of data to store in a single file.
                // We reset the stream
                $written = 0;
                if (is_resource($fp)) {
                    fclose($fp);
                }
            }

            if ($written === 0) {
                // increment dbStep will increment the number in filename
                $this->container->getState()->setDbStep($this->container->getState()->getDbStep() + 1);

                $backupfile = $this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $this->container->getState()->getBackupName() . DIRECTORY_SEPARATOR . $this->container->getState()->getBackupDbFilename();
                $backupfile = preg_replace('#_XXXXXX_#', '_' . str_pad(strval($this->container->getState()->getDbStep()), 6, '0', STR_PAD_LEFT) . '_', $backupfile);

                // start init file
                $fp = $this->openPartialBackupFile($backupfile);

                $written += fwrite($fp, '/* Backup ' . $this->container->getState()->getDbStep() . ' for ' . Tools14::getHttpHost() . __PS_BASE_URI__ . "\n *  at " . date('r') . "\n */\n");
                $written += fwrite($fp, "\n" . 'SET SESSION sql_mode = \'\';' . "\n\n");
                $written += fwrite($fp, "\n" . 'SET NAMES \'utf8\';' . "\n\n");
                $written += fwrite($fp, "\n" . 'SET FOREIGN_KEY_CHECKS=0;' . "\n\n");
                // end init file
            }

            // start schema : drop & create table only
            if (null === $this->container->getState()->getBackupTable()) {
                // Export the table schema
                $schema = $db->executeS('SHOW CREATE TABLE `' . $table . '`', true, false);

                if (count($schema) != 1 ||
                    !(isset($schema[0]['Table'], $schema[0]['Create Table'])
                        || isset($schema[0]['View'], $schema[0]['Create View']))) {
                    fclose($fp);
                    if (file_exists($backupfile)) {
                        unlink($backupfile);
                    }
                    $this->logger->error($this->translator->trans('An error occurred while backing up. Unable to obtain the schema of %s', [$table]));
                    $this->logger->info($this->translator->trans('Error during database backup.'));
                    $this->next = 'error';
                    $this->setErrorFlag();

                    return ExitCode::FAIL;
                }

                // case view
                if (isset($schema[0]['View'])) {
                    $written += fwrite($fp, '/* Scheme for view' . $schema[0]['View'] . " */\n");
                    // If some *upgrade* transform a table in a view, drop both just in case
                    $written += fwrite($fp, 'DROP TABLE IF EXISTS `' . $schema[0]['View'] . '`;' . "\n");
                    $written += fwrite($fp, 'DROP VIEW IF EXISTS `' . $schema[0]['View'] . '`;' . "\n");
                    $written += fwrite($fp, preg_replace('#DEFINER=[^\s]+\s#', 'DEFINER=CURRENT_USER ', $schema[0]['Create View']) . ";\n\n");

                    $ignore_stats_table[] = $schema[0]['View'];
                // There is no data to sync -> setBackupTable is not set.
                }
                // case table
                elseif (isset($schema[0]['Table'])) {
                    // Case common table
                    $written += fwrite($fp, '/* Scheme for table ' . $schema[0]['Table'] . " */\n");
                    // If some *upgrade* transform a table in a view, drop both just in case
                    $written += fwrite($fp, 'DROP TABLE IF EXISTS `' . $schema[0]['Table'] . '`;' . "\n");
                    $written += fwrite($fp, 'DROP VIEW IF EXISTS `' . $schema[0]['Table'] . '`;' . "\n");
                    // CREATE TABLE
                    $written += fwrite($fp, $schema[0]['Create Table'] . ";\n\n");
                    // schema created, now we need to create the missing vars
                    $this->container->getState()->setBackupTable($table);
                    $lines = explode("\n", $schema[0]['Create Table']);
                    $this->container->getState()->setBackupLines($lines);
                }
            }
            // end of schema

            $i = 0;

            // POPULATE TABLE
            if ($this->container->getState()->getBackupTable()) {
                $backup_loop_limit = $this->container->getState()->getBackupLoopLimit();

                $dbLink->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
                /** @see https://dev.mysql.com/doc/refman/8.4/en/select.html specifies a large LIMIT value to get the whole table */
                $data = $dbLink->prepare('SELECT * FROM `' . $table . '` LIMIT ' . (int) $backup_loop_limit . ',18446744073709551615');
                $data->execute();

                $insertStmtSize = 0;

                while (($row = $data->fetch(PDO::FETCH_ASSOC)) && $this->isRemainingTimeEnough($time_elapsed)) {
                    if (!$insertStmtSize) {
                        $written += fwrite($fp, 'INSERT INTO `' . $table . "` VALUES\n");
                    }

                    if ($i && $insertStmtSize) {
                        $s = "),\n(";
                    } else {
                        $s = '(';
                    }
                    ++$i;

                    // this starts a row
                    foreach ($row as $value) {
                        if ($value === null) {
                            $s .= 'NULL,';
                        } else {
                            $s .= "'" . $db->escape($value, true) . "',";
                        }
                    }
                    $s = rtrim($s, ',');

                    $writtenBytes = fwrite($fp, $s);
                    $written += $writtenBytes;
                    $insertStmtSize += $writtenBytes;

                    // If we reach the size limit of a single INSERT INTO statement, we close the list and start a new one.
                    if ($insertStmtSize >= self::MAX_SIZE_PER_INSERT_STMT) {
                        $written += fwrite($fp, ");\n");
                        $insertStmtSize = 0;
                    }
                    fflush($fp);
                    $time_elapsed = time() - $start_time;
                }

                if ($i && $insertStmtSize) {
                    $written += fwrite($fp, ");\n");
                }
            }

            if (!empty($row)) {
                // Still data to store, prepare state
                $this->container->getState()->setBackupLoopLimit($this->container->getState()->getBackupLoopLimit() + $i);
            } else {
                // Sync is complete for the table
                ++$numberOfSyncedTables;
                $this->logger->debug($this->translator->trans('%s table has been saved.', [$table]));
                $this->container->getState()->setBackupTable(null);
            }

            $time_elapsed = time() - $start_time;
        }

        // end of loop
        if (is_resource($fp)) {
            $written += fwrite($fp, "\n" . 'SET FOREIGN_KEY_CHECKS=1;' . "\n\n");
            fclose($fp);
            $fp = null;
        }

        $this->container->getState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->computePercentage($tablesToBackup, self::class, UpgradeFiles::class)
        );
        $this->container->getFileConfigurationStorage()->save($tablesToBackup->dump(), UpgradeFileNames::DB_TABLES_TO_BACKUP_LIST);

        if ($numberOfSyncedTables) {
            $this->logger->info($this->translator->trans('%s tables have been saved.', [$numberOfSyncedTables]));
        }

        if ($tablesToBackup->getRemainingTotal()) {
            $this->next = 'backupDb';
            $this->stepDone = false;
            if ($numberOfSyncedTables) {
                $this->logger->info($this->translator->trans('Database backup: %s table(s) left...', [$tablesToBackup->getRemainingTotal() + !empty($row)]));
            }

            return ExitCode::SUCCESS;
        }
        $this->container->getState()
            ->setBackupLoopLimit(null)
            ->setBackupLines(null)
            ->setBackupTable(null);

        $this->stepDone = true;
        // reset dbStep at the end of this step
        $this->container->getState()->setDbStep(0);

        $this->logger->info($this->translator->trans('Database backup done in filename %s. Now upgrading files...', [$this->container->getState()->getBackupName()]));
        $this->next = 'upgradeFiles';

        $this->container->getAnalytics()->track('Backup Succeeded', Analytics::WITH_BACKUP_PROPERTIES);

        return ExitCode::SUCCESS;
    }

    protected function warmUp(): int
    {
        $this->container->getState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $relative_backup_path = str_replace(_PS_ROOT_DIR_, '', $this->container->getProperty(UpgradeContainer::BACKUP_PATH));
        $report = '';
        if (!\ConfigurationTest::test_dir($relative_backup_path, false, $report)) {
            $this->logger->error($this->translator->trans('Backup directory is not writable (%path%).', ['%path%' => $this->container->getProperty(UpgradeContainer::BACKUP_PATH)]));
            $this->next = 'error';
            $this->setErrorFlag();

            return ExitCode::FAIL;
        }

        if (!is_dir($this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $this->container->getState()->getBackupName())) {
            mkdir($this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $this->container->getState()->getBackupName());
        }
        $this->container->getState()->setDbStep(0);
        $listOfTables = $this->filterTablesToSync(
            $this->container->getDb()->executeS('SHOW TABLES LIKE "' . _DB_PREFIX_ . '%"', true, false)
        );

        if (empty($listOfTables)) {
            throw (new UpgradeException($this->translator->trans('No valid tables were found to back up. Backup of database canceled.')))->setSeverity(UpgradeException::SEVERITY_ERROR);
        }

        $tablesToBackup = new Backlog($listOfTables, count($listOfTables));

        $this->container->getFileConfigurationStorage()->save($tablesToBackup->dump(), UpgradeFileNames::DB_TABLES_TO_BACKUP_LIST);

        return ExitCode::SUCCESS;
    }

    /**
     * @param array<array<string, string>> $listOfTables
     *
     * @internal Method is public for unit tests
     *
     * @return string[]
     */
    public function filterTablesToSync(array $listOfTables): array
    {
        return array_filter(array_map('current', $listOfTables), function ($table) {
            // Skip tables which do not start with _DB_PREFIX_
            if (strlen($table) <= strlen(_DB_PREFIX_) || strncmp($table, _DB_PREFIX_, strlen(_DB_PREFIX_)) !== 0) {
                return false;
            }

            // Ignore stat tables
            if (in_array($table, $this->getTablesToIgnore())) {
                return false;
            }

            return true;
        });
    }

    /**
     * @return string[]
     */
    private function getTablesToIgnore(): array
    {
        return [
            _DB_PREFIX_ . 'connections',
            _DB_PREFIX_ . 'connections_page',
            _DB_PREFIX_ . 'connections_source',
            _DB_PREFIX_ . 'guest',
            _DB_PREFIX_ . 'statssearch',
        ];
    }

    // MANAGEMENT OF BACKUP FILE RESOURCE

    /**
     * @return resource
     *
     * @throws Exception if file already exists or cannot be written
     */
    private function openPartialBackupFile(string $backupfile)
    {
        // Figure out what compression is available and open the file
        if (file_exists($backupfile)) {
            throw (new UpgradeException($this->translator->trans('Backup file %s already exists. Operation aborted.', [$backupfile])))->setSeverity(UpgradeException::SEVERITY_ERROR);
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
            throw (new UpgradeException($this->translator->trans('Unable to create backup database file %s.', [addslashes($backupfile)])))->setSeverity(UpgradeException::SEVERITY_ERROR);
        }

        return $fp;
    }

    private function isRemainingTimeEnough(int $elapsedTime): bool
    {
        $timeAllowed = (int) @ini_get('max_execution_time');

        if ($timeAllowed <= 0) {
            return true;
        }

        // Remove 5 seconds on the allowed time to make sure we have time to save data and close files.
        return $elapsedTime < $timeAllowed - 5;
    }
}
