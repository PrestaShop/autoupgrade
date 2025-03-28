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
use PrestaShop\Module\AutoUpgrade\Database\TableFilter;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Progress\Backlog;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

/**
 * Restores database from backup file.
 */
class RestoreDatabase extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_RESTORE;

    public function init(): void
    {
        // We don't need the whole core being instanciated, only the autoloader
        $this->container->initPrestaShopAutoloader();

        // Loads the parameters.php file on PrestaShop 1.7, needed for accessing the database
        if ($this->container->getFileSystem()->exists($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'bootstrap.php')) {
            require_once $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'bootstrap.php';
        }
    }

    /**
     * @throws Exception
     */
    public function run(): int
    {
        $this->stepDone = false;
        $this->next = TaskName::TASK_RESTORE_DATABASE;
        $startTime = time();

        if (!$this->container->getFileStorage()->exists(UpgradeFileNames::DB_FILES_TO_RESTORE_LIST)) {
            return $this->warmUp();
        }

        $db = $this->container->getDb();

        $dbFilenamesBacklog = Backlog::fromContents($this->container->getFileStorage()->load(UpgradeFileNames::DB_FILES_TO_RESTORE_LIST));
        $queriesBacklog = Backlog::fromContents($this->container->getFileStorage()->load(UpgradeFileNames::QUERIES_TO_RESTORE_LIST));

        if (!$queriesBacklog->getRemainingTotal()) {
            if (!$dbFilenamesBacklog->getRemainingTotal()) {
                $db->execute('SET FOREIGN_KEY_CHECKS=1');
                $this->stepDone = true;
                $this->status = 'ok';
                $this->next = TaskName::TASK_RESTORE_COMPLETE;
                $this->logger->info($this->translator->trans('Database restoration done.'));

                return ExitCode::SUCCESS;
            }

            return $this->loadNextDbFile();
        }

        $db->execute('SET SESSION sql_mode = \'\'');
        $db->execute('SET FOREIGN_KEY_CHECKS=0');

        $time_elapsed = time() - $startTime;

        while ($time_elapsed < $this->container->getUpdateConfiguration()->getTimePerCall() && $queriesBacklog->getRemainingTotal() > 0) {
            $query = trim($queriesBacklog->getNext());
            if (!empty($query) && !$db->execute($query, false)) {
                $this->logger->error($this->translator->trans('Error during database restoration: ') . ' ' . $query . ' - ' . $this->container->getDb()->getMsgError());
                $this->setErrorFlag();

                return ExitCode::FAIL;
            }

            $time_elapsed = time() - $startTime;
        }

        $this->container->getFileStorage()->save($queriesBacklog->dump(), UpgradeFileNames::QUERIES_TO_RESTORE_LIST);

        return ExitCode::SUCCESS;
    }

    /**
     * @throws Exception
     */
    protected function warmUp(): int
    {
        $state = $this->container->getRestoreState();

        $state->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $restoreDbFilenames = $state->getRestoreDbFilenames();

        $dbFilenamesBacklog = new Backlog(array_reverse($restoreDbFilenames), count($restoreDbFilenames));
        $this->container->getFileStorage()->save($dbFilenamesBacklog->dump(), UpgradeFileNames::DB_FILES_TO_RESTORE_LIST);

        $this->cleanDb();

        return $this->loadNextDbFile();
    }

    /**
     * @throws Exception
     */
    private function loadNextDbFile(): int
    {
        $dbFilenamesBacklog = Backlog::fromContents($this->container->getFileStorage()->load(UpgradeFileNames::DB_FILES_TO_RESTORE_LIST));
        $nextDbFilename = $dbFilenamesBacklog->getNext();

        $state = $this->container->getRestoreState();

        if (!preg_match('#' . BackupFinder::BACKUP_DB_FOLDER_NAME_PREFIX . '([0-9]{6})_#', $nextDbFilename, $fileNumber)) {
            $this->next = TaskName::TASK_ERROR;
            $this->setErrorFlag();
            $this->logger->error($this->translator->trans('%s: File format does not match.', [$nextDbFilename]));

            return ExitCode::FAIL;
        }

        $backupDbPath = $this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $state->getRestoreName();

        $fullFilePath = $backupDbPath . DIRECTORY_SEPARATOR . $nextDbFilename;
        $fileExtension = pathinfo($fullFilePath, PATHINFO_EXTENSION);
        $content = '';

        $this->logger->debug($this->translator->trans(
            'Opening backup database file %filename% in %extension% mode',
            [
                '%filename%' => $nextDbFilename,
                '%extension%' => $fileExtension,
            ]
        ));

        switch ($fileExtension) {
            case 'bz':
            case 'bz2':
                $fp = bzopen($fullFilePath, 'r');
                if (is_resource($fp)) {
                    while (!feof($fp)) {
                        $content .= bzread($fp, 4096);
                    }
                    bzclose($fp);
                }
                break;
            case 'gz':
                $fp = gzopen($fullFilePath, 'r');
                if (is_resource($fp)) {
                    while (!feof($fp)) {
                        $content .= gzread($fp, 4096);
                    }
                    gzclose($fp);
                }
                break;
            default:
                $fp = fopen($fullFilePath, 'r');
                if (is_resource($fp)) {
                    while (!feof($fp)) {
                        $content .= fread($fp, 4096);
                    }
                    fclose($fp);
                }
        }

        if (empty($content)) {
            $this->logger->error($this->translator->trans('Database backup is empty.'));
            $this->next = TaskName::TASK_ERROR;

            return ExitCode::FAIL;
        }

        // preg_match_all is better than preg_split (what is used in do Upgrade.php)
        // This way we avoid extra blank lines
        // option s (PCRE_DOTALL) added
        $listQuery = preg_split('/;[\n\r]+/Usm', $content);
        unset($content);

        $queriesBacklog = new Backlog(array_reverse($listQuery), count($listQuery));

        $state->setDbStep((int) $fileNumber[1]);
        $this->container->getFileStorage()->save($dbFilenamesBacklog->dump(), UpgradeFileNames::DB_FILES_TO_RESTORE_LIST);
        $this->container->getFileStorage()->save($queriesBacklog->dump(), UpgradeFileNames::QUERIES_TO_RESTORE_LIST);

        return ExitCode::SUCCESS;
    }

    private function cleanDb(): void
    {
        $db = $this->container->getDb();
        $tables = $db->executes("SHOW TABLES LIKE '" . _DB_PREFIX_ . "%'");

        if (!$tables) {
            $this->logger->warning($this->translator->trans('No tables matching the prefix "%s" were found in the database.', [_DB_PREFIX_]));
        }

        foreach ($tables as $tableRow) {
            $tableName = reset($tableRow);

            if (!in_array($tableName, TableFilter::tablesToIgnore(), true)) {
                $db->execute('DROP TABLE IF EXISTS `' . bqSql($tableName) . '`');
                $db->execute('DROP VIEW IF EXISTS `' . bqSql($tableName) . '`');
            }
        }
    }
}
