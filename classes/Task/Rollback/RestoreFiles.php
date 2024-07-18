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

namespace PrestaShop\Module\AutoUpgrade\Task\Rollback;

use Exception;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

/**
 * ajaxProcessRestoreFiles restore the previously saved files,
 * and delete files that weren't archived.
 */
class RestoreFiles extends AbstractTask
{
    const TASK_TYPE = 'rollback';

    /**
     * @throws Exception
     */
    public function run(): int
    {
        $this->container->getState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        // loop
        $this->next = 'restoreFiles';
        if (!file_exists($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::FILES_FROM_ARCHIVE_LIST)
            || !file_exists($this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::FILES_TO_REMOVE_LIST)) {
            // cleanup current PS tree
            $fromArchive = $this->container->getZipAction()->listContent($this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $this->container->getState()->getRestoreFilesFilename());
            foreach ($fromArchive as $k => $v) {
                $fromArchive[DIRECTORY_SEPARATOR . $v] = DIRECTORY_SEPARATOR . $v;
            }

            $this->container->getFileConfigurationStorage()->save($fromArchive, UpgradeFileNames::FILES_FROM_ARCHIVE_LIST);
            // get list of files to remove
            $toRemove = $this->container->getFilesystemAdapter()->listFilesToRemove();
            $toRemoveOnly = [];

            // let's reverse the array in order to make possible to rmdir
            // remove fullpath. This will be added later in the loop.
            // we do that for avoiding fullpath to be revealed in a text file
            foreach ($toRemove as $k => $v) {
                $vfile = str_replace($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH), '', $v);
                $toRemove[] = str_replace($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH), '', $vfile);

                if (!isset($fromArchive[$vfile]) && is_file($v)) {
                    $toRemoveOnly[$vfile] = str_replace($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH), '', $vfile);
                }
            }

            $this->logger->debug($this->translator->trans('%s file(s) will be removed before restoring the backup files.', [count($toRemoveOnly)]));
            $this->container->getFileConfigurationStorage()->save($toRemoveOnly, UpgradeFileNames::FILES_TO_REMOVE_LIST);

            if (empty($fromArchive) || empty($toRemove)) {
                if (empty($fromArchive)) {
                    $this->logger->error($this->translator->trans('[ERROR] Backup file %s does not exist.', [UpgradeFileNames::FILES_FROM_ARCHIVE_LIST]));
                }
                if (empty($toRemove)) {
                    $this->logger->error($this->translator->trans('[ERROR] File "%s" does not exist.', [UpgradeFileNames::FILES_TO_REMOVE_LIST]));
                }
                $this->logger->info($this->translator->trans('Unable to remove upgraded files.'));
                $this->next = 'error';
                $this->setErrorFlag();

                return ExitCode::FAIL;
            }
        }

        if (!empty($fromArchive)) {
            $filepath = $this->container->getProperty(UpgradeContainer::BACKUP_PATH) . DIRECTORY_SEPARATOR . $this->container->getState()->getRestoreFilesFilename();
            $destExtract = $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH);

            $res = $this->container->getZipAction()->extract($filepath, $destExtract);
            if (!$res) {
                $this->next = 'error';
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

            if (!empty($toRemoveOnly)) {
                foreach ($toRemoveOnly as $fileToRemove) {
                    @unlink($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . $fileToRemove);
                }
            }

            $this->next = 'restoreDb';
            $this->logger->debug($this->translator->trans('Files restored.'));
            $this->logger->info($this->translator->trans('Files restored. Now restoring database...'));
        }

        return ExitCode::SUCCESS;
    }

    public function init(): void
    {
        // Do nothing
    }
}
