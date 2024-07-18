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
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\Progress\Backlog;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;

class UpgradeFiles extends AbstractTask
{
    const TASK_TYPE = 'upgrade';

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
        if (!$this->container->getFileConfigurationStorage()->exists(UpgradeFileNames::FILES_TO_UPGRADE_LIST)) {
            return $this->warmUp();
        }

        // later we could choose between _PS_ROOT_DIR_ or _PS_TEST_DIR_
        $this->destUpgradePath = $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH);

        $this->next = 'upgradeFiles';

        // Now we load the list of files to be upgraded, prepared previously by warmUp method.
        $filesToUpgrade = Backlog::fromContents(
            $this->container->getFileConfigurationStorage()->load(UpgradeFileNames::FILES_TO_UPGRADE_LIST)
        );

        // @TODO : does not upgrade files in modules, translations if they have not a correct md5 (or crc32, or whatever) from previous version
        for ($i = 0; $i < $this->container->getUpgradeConfiguration()->getNumberOfFilesPerCall(); ++$i) {
            if (!$filesToUpgrade->getRemainingTotal()) {
                $this->next = 'upgradeDb';
                $this->logger->info($this->translator->trans('All files upgraded. Now upgrading database...'));
                $this->stepDone = true;
                break;
            }

            $file = $filesToUpgrade->getNext();

            // Note - upgrade this file means do whatever is needed for that file to be in the final state, delete included.
            if (!$this->upgradeThisFile($file)) {
                // put the file back to the begin of the list
                $this->next = 'error';
                $this->logger->error($this->translator->trans('Error when trying to upgrade file %s.', [$file]));
                break;
            }
        }
        $this->container->getState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->computePercentage($filesToUpgrade, self::class, UpgradeDb::class)
        );
        $this->container->getFileConfigurationStorage()->save($filesToUpgrade->dump(), UpgradeFileNames::FILES_TO_UPGRADE_LIST);

        $countOfRemainingBacklog = $filesToUpgrade->getRemainingTotal();
        if ($countOfRemainingBacklog > 0) {
            $this->logger->info($this->translator->trans('%s files left to upgrade.', [$countOfRemainingBacklog]));
            $this->stepDone = false;
        }

        return $this->next == 'error' ? ExitCode::FAIL : ExitCode::SUCCESS;
    }

    /**
     * upgradeThisFile.
     *
     * @param mixed $orig The absolute path to the file from the upgrade archive
     *
     * @throws Exception
     */
    public function upgradeThisFile($orig): bool
    {
        // translations_custom and mails_custom list are currently not used
        // later, we could handle customization with some kind of diff functions
        // for now, just copy $file in str_replace($this->latestRootDir,_PS_ROOT_DIR_)

        $file = str_replace($this->container->getProperty(UpgradeContainer::LATEST_PATH), '', $orig);

        // The path to the file in our prestashop directory
        $dest = $this->destUpgradePath . $file;

        // Skip files that we want to avoid touching. They may be already excluded from the list from before,
        // but again, as a safety precaution.
        if ($this->container->getFilesystemAdapter()->isFileSkipped($file, $dest, 'upgrade')) {
            $this->logger->debug($this->translator->trans('%s ignored', [$file]));

            return true;
        }
        if (is_dir($orig)) {
            // if $dest is not a directory (that can happen), just remove that file
            if (!is_dir($dest) && file_exists($dest)) {
                unlink($dest);
                $this->logger->debug($this->translator->trans('[WARNING] File %1$s has been deleted.', [$file]));
            }
            if (!file_exists($dest)) {
                if (mkdir($dest)) {
                    $this->logger->debug($this->translator->trans('Directory %1$s created.', [$file]));

                    return true;
                } else {
                    $this->next = 'error';
                    $this->logger->error($this->translator->trans('Error while creating directory %s.', [$dest]));

                    return false;
                }
            } else { // directory already exists
                $this->logger->debug($this->translator->trans('Directory %s already exists.', [$file]));

                return true;
            }
        } elseif (is_file($orig)) {
            $translationAdapter = $this->container->getTranslationAdapter();
            if ($translationAdapter->isTranslationFile($file) && file_exists($dest)) {
                $type_trad = $translationAdapter->getTranslationFileType($file);
                if ($translationAdapter->mergeTranslationFile($orig, $dest, $type_trad)) {
                    $this->logger->info($this->translator->trans('[TRANSLATION] The translation files have been merged into file %s.', [$dest]));

                    return true;
                }
                $this->logger->warning($this->translator->trans(
                    '[TRANSLATION] The translation files have not been merged into file %filename%. Switch to copy %filename%.',
                    ['%filename%' => $dest]
                ));
            }

            // upgrade exception were above. This part now process all files that have to be upgraded (means to modify or to remove)
            // delete before updating (and this will also remove deprecated files)
            if (copy($orig, $dest)) {
                $this->logger->debug($this->translator->trans('Copied %1$s.', [$file]));

                return true;
            } else {
                $this->next = 'error';
                $this->logger->error($this->translator->trans('Error while copying file %s', [$file]));

                return false;
            }
        } elseif (is_file($dest)) {
            if (file_exists($dest)) {
                unlink($dest);
            }
            $this->logger->debug(sprintf('removed file %1$s.', $file));

            return true;
        } elseif (is_dir($dest)) {
            FilesystemAdapter::deleteDirectory($dest);
            $this->logger->debug(sprintf('removed dir %1$s.', $file));

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
        $this->container->getState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        // Get path to the folder with release we will use to upgrade and check if it's valid
        $newReleasePath = $this->container->getProperty(UpgradeContainer::LATEST_PATH);
        if (!$this->container->getFilesystemAdapter()->isReleaseValid($newReleasePath)) {
            $this->logger->error($this->translator->trans('Could not assert the folder %s contains a valid PrestaShop release, exiting.', [$newReleasePath]));
            $this->logger->error($this->translator->trans('A file may be missing, or the release is stored in a subfolder by mistake.'));
            $this->next = 'error';

            return ExitCode::FAIL;
        }

        // Replace the name of the admin folder inside the release to match our admin folder name
        $admin_dir = str_replace($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . DIRECTORY_SEPARATOR, '', $this->container->getProperty(UpgradeContainer::PS_ADMIN_PATH));
        if (file_exists($newReleasePath . DIRECTORY_SEPARATOR . 'admin')) {
            rename($newReleasePath . DIRECTORY_SEPARATOR . 'admin', $newReleasePath . DIRECTORY_SEPARATOR . $admin_dir);
        } elseif (file_exists($newReleasePath . DIRECTORY_SEPARATOR . 'admin-dev')) {
            rename($newReleasePath . DIRECTORY_SEPARATOR . 'admin-dev', $newReleasePath . DIRECTORY_SEPARATOR . $admin_dir);
        }

        // Rename develop installer directory, it would be ignored anyway because it's present in getFilesToIgnoreOnUpgrade()
        if (file_exists($newReleasePath . DIRECTORY_SEPARATOR . 'install-dev')) {
            rename($newReleasePath . DIRECTORY_SEPARATOR . 'install-dev', $newReleasePath . DIRECTORY_SEPARATOR . 'install');
        }

        // Now, we will get the list of changed and removed files between the versions. This was generated previously by
        // CompareReleases task.
        $filepath_list_diff = $this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::FILES_DIFF_LIST;
        $list_files_diff = [];

        // We check if that file exists first and load it
        if (file_exists($filepath_list_diff)) {
            $list_files_diff = $this->container->getFileConfigurationStorage()->load(UpgradeFileNames::FILES_DIFF_LIST);
            // $list_files_diff now contains an array with a list of changed and deleted files.
            // We only keep list of files to delete. The modified files will be listed in list_files_to_upgrade below.
            $list_files_diff = $list_files_diff['deleted'];

            // Admin folder name in this deleted files list is standard /admin/.
            // We will need to change it to our own admin folder name.
            $admin_dir = trim(str_replace($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH), '', $this->container->getProperty(UpgradeContainer::PS_ADMIN_PATH)), DIRECTORY_SEPARATOR);
            foreach ($list_files_diff as $k => $path) {
                if (preg_match('#autoupgrade#', $path)) {
                    unset($list_files_diff[$k]);
                } elseif (substr($path, 0, 6) === '/admin') {
                    // Please make sure that the condition to check if the string starts with /admin stays here, because it was replacing
                    // admin even in the middle of a path, not deleting some files as a result.
                    // Also, do not use DIRECTORY_SEPARATOR, keep forward slash, because the path come from the XML standardized.
                    $list_files_diff[$k] = '/' . $admin_dir . substr($path, 6);
                }
            }
        }

        // Now, we get the list of files that are either new or must be modified
        $list_files_to_upgrade = $this->container->getFilesystemAdapter()->listFilesInDir(
            $newReleasePath, 'upgrade', true
        );

        // Add our previously created list of deleted files
        $list_files_to_upgrade = array_reverse(array_merge($list_files_diff, $list_files_to_upgrade));

        $total_files_to_upgrade = count($list_files_to_upgrade);
        $this->container->getFileConfigurationStorage()->save(
            (new Backlog($list_files_to_upgrade, $total_files_to_upgrade))->dump(),
            UpgradeFileNames::FILES_TO_UPGRADE_LIST
        );

        if ($total_files_to_upgrade === 0) {
            $this->logger->error($this->translator->trans('[ERROR] Unable to find files to upgrade.'));
            $this->next = 'error';

            return ExitCode::FAIL;
        }
        $this->logger->info($this->translator->trans('%s files will be upgraded.', [$total_files_to_upgrade]));
        $this->next = 'upgradeFiles';
        $this->stepDone = false;

        return ExitCode::SUCCESS;
    }

    public function init(): void
    {
        // Do nothing. Overrides parent init for avoiding core to be loaded here.
    }
}
