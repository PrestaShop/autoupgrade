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
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Progress\Backlog;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\TaskName;
use PrestaShop\Module\AutoUpgrade\Task\TaskType;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use Symfony\Component\Filesystem\Exception\IOException;

class UpdateFiles extends AbstractTask
{
    const TASK_TYPE = TaskType::TASK_TYPE_UPDATE;

    /**
     * @var string
     */
    private $destUpgradePath;

    /**
     * @throws Exception
     */
    public function run(): int
    {
        // The first call must init the list of files be upgraded.
        if (!$this->container->getFileStorage()->exists(UpgradeFileNames::FILES_TO_UPGRADE_LIST)) {
            return $this->warmUp();
        }

        // later we could choose between _PS_ROOT_DIR_ or _PS_TEST_DIR_
        $this->destUpgradePath = $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH);

        $this->next = TaskName::TASK_UPDATE_FILES;

        // Now we load the list of files to be upgraded, prepared previously by warmUp method.
        $filesToUpgrade = Backlog::fromContents(
            $this->container->getFileStorage()->load(UpgradeFileNames::FILES_TO_UPGRADE_LIST)
        );

        // @TODO : does not upgrade files in modules, translations if they have not a correct md5 (or crc32, or whatever) from previous version
        for ($i = 0; $i < $this->container->getUpdateConfiguration()->getNumberOfFilesPerCall(); ++$i) {
            if (!$filesToUpgrade->getRemainingTotal()) {
                $this->next = TaskName::TASK_UPDATE_DATABASE;
                $this->logger->info($this->translator->trans('All files updated. Now updating database...'));
                $this->stepDone = true;
                break;
            }

            $file = $filesToUpgrade->getNext();

            // Note - upgrade this file means do whatever is needed for that file to be in the final state, delete included.
            if (!$this->upgradeThisFile($file)) {
                $this->next = TaskName::TASK_ERROR;
                $this->logger->error($this->translator->trans('Error when trying to update file %s.', [$file]));
                break;
            }
        }
        $this->container->getUpdateState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->computePercentage($filesToUpgrade, self::class, UpdateDatabase::class)
        );
        $this->container->getFileStorage()->save($filesToUpgrade->dump(), UpgradeFileNames::FILES_TO_UPGRADE_LIST);

        $countOfRemainingBacklog = $filesToUpgrade->getRemainingTotal();
        if ($countOfRemainingBacklog > 0) {
            $this->logger->info($this->translator->trans('%s files left to update.', [$countOfRemainingBacklog]));
            $this->stepDone = false;
        }

        return $this->next == TaskName::TASK_ERROR ? ExitCode::FAIL : ExitCode::SUCCESS;
    }

    /**
     * upgradeThisFile.
     *
     * @param mixed $orig The absolute path to the file from the upgrade archive
     *
     * @throws Exception
     */
    protected function upgradeThisFile($orig): bool
    {
        // translations_custom and mails_custom list are currently not used
        // later, we could handle customization with some kind of diff functions
        // for now, just copy $file in str_replace($this->latestRootDir,_PS_ROOT_DIR_)

        $file = str_replace($this->container->getProperty(UpgradeContainer::TMP_FILES_PATH), '', $orig);

        // The path to the file in our prestashop directory
        $dest = $this->destUpgradePath . $file;

        // Skip files that we want to avoid touching. They may be already excluded from the list from before,
        // but again, as a safety precaution.
        if ($this->container->getFilesystemAdapter()->isFileSkipped($file, $dest, 'upgrade')) {
            $this->logger->debug($this->translator->trans('%s ignored', [$file]));

            return true;
        }

        if (is_link($orig)) {
            $rawSymlink = readlink($orig);
            // Windows resolves the symlink target to an absolute path. We restore the original path.
            $symLinkTarget = $this->container->getFileSystem()->isAbsolutePath($rawSymlink)
                ? $this->container->getFileSystem()->makePathRelative($rawSymlink, pathinfo($orig, PATHINFO_DIRNAME))
                : $rawSymlink;

            try {
                if ($this->container->getFileSystem()->exists($dest)) {
                    $this->container->getFileSystem()->remove($dest);
                }

                $this->container->getFileSystem()->symlink($symLinkTarget, $dest);
                $this->logger->debug($this->translator->trans('Created link %s to %s.', [$file, $symLinkTarget]));

                return true;
            } catch (IOException $e) {
                $this->next = TaskName::TASK_ERROR;
                $this->logger->error($this->translator->trans('Error while creating link %s: %s', [$file, $e->getMessage()]));

                return false;
            }
        } elseif (is_dir($orig)) {
            // if $dest is not a directory (that can happen), just remove that file
            if (!is_dir($dest) && $this->container->getFileSystem()->exists($dest)) {
                $this->container->getFileSystem()->remove($dest);
                $this->logger->debug($this->translator->trans('File %1$s has been deleted.', [$file]));
            }
            if (!$this->container->getFileSystem()->exists($dest)) {
                try {
                    $this->container->getFileSystem()->mkdir($dest);
                    $this->logger->debug($this->translator->trans('Directory %1$s created.', [$file]));

                    return true;
                } catch (IOException $e) {
                    $this->next = TaskName::TASK_ERROR;
                    $this->logger->error($this->translator->trans('Error while creating directory %s: %s.', [$dest, $e->getMessage()]));

                    return false;
                }
            } else { // directory already exists
                $this->logger->debug($this->translator->trans('Directory %s already exists.', [$file]));

                return true;
            }
        } elseif (is_file($orig)) {
            $translationAdapter = $this->container->getTranslationAdapter();
            if ($translationAdapter->isTranslationFile($file) && $this->container->getFileSystem()->exists($dest)) {
                $type_trad = $translationAdapter->getTranslationFileType($file);
                if ($translationAdapter->mergeTranslationFile($orig, $dest, $type_trad)) {
                    $this->logger->info($this->translator->trans('The translation files have been merged into file %s.', [$dest]));

                    return true;
                }
                $this->logger->warning($this->translator->trans(
                    'The translation files have not been merged into file %filename%. Switch to copy %filename%.',
                    ['%filename%' => $dest]
                ));
            }

            // upgrade exception were above. This part now process all files that have to be upgraded (means to modify or to remove)
            // delete before updating (and this will also remove deprecated files)
            try {
                $this->container->getFileSystem()->copy($orig, $dest, true);
                $this->logger->debug($this->translator->trans('Copied %1$s.', [$file]));

                return true;
            } catch (IOException $e) {
                $checksumOrig = md5_file($orig);
                $checksumDest = md5_file($dest);

                $isFileUnchanged = $checksumOrig === $checksumDest;

                if ($isFileUnchanged) {
                    $this->logger->warning($this->translator->trans('Unable to copy %s, but ignoring as source and destination appear identical.', [$file]));

                    return true;
                }

                $this->next = TaskName::TASK_ERROR;
                $this->logger->error($this->translator->trans('Error while copying file %s: %s', [$file, $e->getMessage()]));

                return false;
            }
        } elseif (is_file($dest)) {
            $this->container->getFileSystem()->remove($dest);
            $this->logger->debug(sprintf('Removed file %1$s.', $file));

            return true;
        } elseif (is_dir($dest)) {
            $this->container->getFileSystem()->remove($dest);
            $this->logger->debug(sprintf('Removed dir %1$s.', $file));

            return true;
        } else {
            return true;
        }
    }

    /**
     * First call of this task needs a warmup, where we load the files list to be upgraded.
     *
     * @throws Exception
     */
    protected function warmUp(): int
    {
        $state = $this->container->getUpdateState();
        $state->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $updateConfiguration = $this->container->getUpdateConfiguration();
        if ($updateConfiguration->isChannelLocal()) {
            $archiveXml = $updateConfiguration->getLocalChannelXml();
            $this->container->getFileLoader()->addXmlMd5File($this->container->getUpgrader()->getDestinationVersion(), $this->container->getProperty(UpgradeContainer::DOWNLOAD_PATH) . DIRECTORY_SEPARATOR . $archiveXml);
        }

        // Get path to the folder with release we will use to upgrade and check if it's valid
        $newReleasePath = $this->container->getProperty(UpgradeContainer::TMP_FILES_PATH);
        if (!$this->container->getFilesystemAdapter()->isReleaseValid($newReleasePath)) {
            $this->logger->error($this->translator->trans('Could not assert the folder %s contains a valid PrestaShop release, exiting.', [$newReleasePath]));
            $this->logger->error($this->translator->trans('A file may be missing, or the release is stored in a subfolder by mistake.'));
            $this->next = TaskName::TASK_ERROR;

            return ExitCode::FAIL;
        }

        // Replace the name of the admin folder inside the release to match our admin folder name
        $admin_dir = str_replace($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . DIRECTORY_SEPARATOR, '', $this->container->getProperty(UpgradeContainer::PS_ADMIN_PATH));
        if ($this->container->getFileSystem()->exists($newReleasePath . DIRECTORY_SEPARATOR . 'admin')) {
            $this->container->getFileSystem()->rename($newReleasePath . DIRECTORY_SEPARATOR . 'admin', $newReleasePath . DIRECTORY_SEPARATOR . $admin_dir);
        } elseif ($this->container->getFileSystem()->exists($newReleasePath . DIRECTORY_SEPARATOR . 'admin-dev')) {
            $this->container->getFileSystem()->rename($newReleasePath . DIRECTORY_SEPARATOR . 'admin-dev', $newReleasePath . DIRECTORY_SEPARATOR . $admin_dir);
        }

        // Rename develop installer directory, it would be ignored anyway because it's present in getFilesToIgnoreOnUpgrade()
        if ($this->container->getFileSystem()->exists($newReleasePath . DIRECTORY_SEPARATOR . 'install-dev')) {
            $this->container->getFileSystem()->rename($newReleasePath . DIRECTORY_SEPARATOR . 'install-dev', $newReleasePath . DIRECTORY_SEPARATOR . 'install');
        }

        $destinationVersion = $state->getDestinationVersion();
        $originVersion = $state->getCurrentVersion();
        $this->logger->debug($this->translator->trans('Generate diff file list between %s and %s.', [$originVersion, $destinationVersion]));
        $diffFileList = $this->container->getChecksumCompare()->getFilesDiffBetweenVersions($originVersion, $destinationVersion);
        if (!is_array($diffFileList)) {
            $this->logger->error($this->translator->trans('Unable to generate diff file list between %s and %s.', [$originVersion, $destinationVersion]));
            $this->next = TaskName::TASK_ERROR;
            $this->setErrorFlag();

            return ExitCode::FAIL;
        }
        // $diffFileList now contains an array with a list of changed and deleted files.
        // We only keep list of files to delete. The modified files will be listed in listFilesToUpgrade below.
        $diffFileList = $diffFileList['deleted'];

        // Admin folder name in this deleted files list is standard /admin/.
        // We will need to change it to our own admin folder name.
        $admin_dir = trim(str_replace($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH), '', $this->container->getProperty(UpgradeContainer::PS_ADMIN_PATH)), DIRECTORY_SEPARATOR);
        foreach ($diffFileList as $k => $path) {
            if (preg_match('#autoupgrade#', $path)) {
                unset($diffFileList[$k]);
            } elseif (substr($path, 0, 6) === '/admin') {
                // Please make sure that the condition to check if the string starts with /admin stays here, because it was replacing
                // admin even in the middle of a path, not deleting some files as a result.
                // Also, do not use DIRECTORY_SEPARATOR, keep forward slash, because the path come from the XML standardized.
                $diffFileList[$k] = '/' . $admin_dir . substr($path, 6);
            }
        }

        // Now, we get the list of files that are either new or must be modified
        $listFilesToUpgrade = $this->container->getFilesystemAdapter()->listFilesInDir(
            $newReleasePath, 'upgrade', true
        );

        // Add our previously created list of deleted files
        $listFilesToUpgrade = array_reverse(array_merge($diffFileList, $listFilesToUpgrade));

        $totalFilesToUpgrade = count($listFilesToUpgrade);
        $this->container->getFileStorage()->save(
            (new Backlog($listFilesToUpgrade, $totalFilesToUpgrade))->dump(),
            UpgradeFileNames::FILES_TO_UPGRADE_LIST
        );

        if ($totalFilesToUpgrade === 0) {
            $this->logger->error($this->translator->trans('Unable to find files to update.'));
            $this->next = TaskName::TASK_ERROR;

            return ExitCode::FAIL;
        }
        $this->logger->info($this->translator->trans('%s files will be updated.', [$totalFilesToUpgrade]));
        $this->next = TaskName::TASK_UPDATE_FILES;
        $this->stepDone = false;

        return ExitCode::SUCCESS;
    }
}
