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
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

/**
 * ajaxProcessRestoreFiles restore the previously saved files,
 * and delete files that weren't archived.
 */
class RestoreFiles extends AbstractTask
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

        // loop
        $this->next = TaskName::TASK_RESTORE_FILES;
        if (!$this->container->getFileSystem()->exists($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::FILES_FROM_ARCHIVE_LIST)
            || !$this->container->getFileSystem()->exists($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::FILES_TO_REMOVE_LIST)) {
            // cleanup current PS tree
            $fromArchive = $this->container->getZipAction()->listContent($this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $state->getRestoreFilesFilename());
            foreach ($fromArchive as $k => $v) {
                $fromArchive[DIRECTORY_SEPARATOR . $v] = DIRECTORY_SEPARATOR . $v;
            }

            $this->container->getFileStorage()->save($fromArchive, UpgradeFileNames::FILES_FROM_ARCHIVE_LIST);
            // get list of files to remove
            $toRemove = $this->container->getFilesystemAdapter()->listFilesToRemove();
            $toRemoveOnly = [];

            // let's reverse the array in order to make possible to rmdir
            // remove fullpath. This will be added later in the loop.
            // we do that for avoiding fullpath to be revealed in a text file
            foreach ($toRemove as $k => $v) {
                $vfile = str_replace($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH), '', $v);
                $toRemove[] = str_replace($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH), '', $vfile);

                if (!isset($fromArchive[$vfile]) && (is_file($v) || is_link($v))) {
                    $toRemoveOnly[$vfile] = str_replace($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH), '', $vfile);
                }
            }

            $this->logger->debug($this->translator->trans('%s file(s) will be removed before restoring the backup files.', [count($toRemoveOnly)]));
            $this->container->getFileStorage()->save($toRemoveOnly, UpgradeFileNames::FILES_TO_REMOVE_LIST);

            if (empty($fromArchive) || empty($toRemove)) {
                if (empty($fromArchive)) {
                    $this->logger->error($this->translator->trans('Backup file %s does not exist.', [UpgradeFileNames::FILES_FROM_ARCHIVE_LIST]));
                }
                if (empty($toRemove)) {
                    $this->logger->error($this->translator->trans('File "%s" does not exist.', [UpgradeFileNames::FILES_TO_REMOVE_LIST]));
                }
                $this->logger->info($this->translator->trans('Unable to remove updated files.'));
                $this->next = TaskName::TASK_ERROR;
                $this->setErrorFlag();

                return ExitCode::FAIL;
            }
        }

        if (!empty($fromArchive)) {
            $filepath = $this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $state->getRestoreFilesFilename();
            $destExtract = $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH);

            if (!empty($toRemoveOnly)) {
                foreach ($toRemoveOnly as $fileToRemove) {
                    $this->container->getFileSystem()->remove($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . $fileToRemove);
                }
            }

            $res = $this->container->getZipAction()->extract($filepath, $destExtract);
            if (!$res) {
                $this->next = TaskName::TASK_ERROR;
                $this->setErrorFlag();
                $this->logger->error($this->translator->trans(
                    'Unable to extract file %filename% into directory %directoryname%.',
                    [
                        '%filename%' => $filepath,
                        '%directoryname%' => $destExtract,
                    ]
                ));

                return ExitCode::FAIL;
            }

            $this->next = TaskName::TASK_RESTORE_DATABASE;
            $this->logger->debug($this->translator->trans('Files restored.'));
            $this->logger->info($this->translator->trans('Files restored. Now restoring database...'));
        }

        return ExitCode::SUCCESS;
    }
}
