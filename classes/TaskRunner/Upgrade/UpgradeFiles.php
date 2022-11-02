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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;

class UpgradeFiles extends AbstractTask
{
    private $destUpgradePath;

    public function run()
    {
        // The first call must init the list of files be upgraded
        if (!$this->container->getFileConfigurationStorage()->exists(UpgradeFileNames::FILES_TO_UPGRADE_LIST)) {
            return $this->warmUp();
        }

        // later we could choose between _PS_ROOT_DIR_ or _PS_TEST_DIR_
        $this->destUpgradePath = $this->container->getProperty(UpgradeContainer::PS_ROOT_PATH);

        $this->next = 'upgradeFiles';
        $filesToUpgrade = $this->container->getFileConfigurationStorage()->load(UpgradeFileNames::FILES_TO_UPGRADE_LIST);
        if (!is_array($filesToUpgrade)) {
            $this->next = 'error';
            $this->logger->error($this->translator->trans('filesToUpgrade is not an array', [], 'Modules.Autoupgrade.Admin'));

            return false;
        }

        // @TODO : does not upgrade files in modules, translations if they have not a correct md5 (or crc32, or whatever) from previous version
        for ($i = 0; $i < $this->container->getUpgradeConfiguration()->getNumberOfFilesPerCall(); ++$i) {
            if (count($filesToUpgrade) <= 0) {
                $this->next = 'upgradeDb';
                if (file_exists(UpgradeFileNames::FILES_TO_UPGRADE_LIST)) {
                    unlink(UpgradeFileNames::FILES_TO_UPGRADE_LIST);
                }
                $this->logger->info($this->translator->trans('All files upgraded. Now upgrading database...', [], 'Modules.Autoupgrade.Admin'));
                $this->stepDone = true;
                break;
            }

            $file = array_pop($filesToUpgrade);
            if (!$this->upgradeThisFile($file)) {
                // put the file back to the begin of the list
                $this->next = 'error';
                $this->logger->error($this->translator->trans('Error when trying to upgrade file %s.', [$file], 'Modules.Autoupgrade.Admin'));
                break;
            }
        }
        $this->container->getFileConfigurationStorage()->save($filesToUpgrade, UpgradeFileNames::FILES_TO_UPGRADE_LIST);
        if (count($filesToUpgrade) > 0) {
            $this->logger->info($this->translator->trans('%s files left to upgrade.', [count($filesToUpgrade)], 'Modules.Autoupgrade.Admin'));
            $this->stepDone = false;
        }

        return true;
    }

    /**
     * list files to upgrade and return it as array
     * TODO: This method needs probably to be moved in FilesystemAdapter.
     *
     * @param string $dir
     *
     * @return array|false Number of files found, or false if param is not a folder
     */
    protected function listFilesToUpgrade($dir)
    {
        $list = [];
        if (!is_dir($dir)) {
            $this->logger->error($this->translator->trans('[ERROR] %s does not exist or is not a directory.', [$dir], 'Modules.Autoupgrade.Admin'));
            $this->logger->info($this->translator->trans('Nothing has been extracted. It seems the unzipping step has been skipped.', [], 'Modules.Autoupgrade.Admin'));
            $this->next = 'error';

            return false;
        }

        $allFiles = scandir($dir);
        foreach ($allFiles as $file) {
            $fullPath = $dir . DIRECTORY_SEPARATOR . $file;

            if ($this->container->getFilesystemAdapter()->isFileSkipped(
                $file,
                $fullPath,
                'upgrade',
                $this->container->getProperty(UpgradeContainer::LATEST_PATH)
            )) {
                if (!in_array($file, ['.', '..'])) {
                    $this->logger->debug($this->translator->trans('File %s is preserved', [$file], 'Modules.Autoupgrade.Admin'));
                }
                continue;
            }
            $list[] = str_replace($this->container->getProperty(UpgradeContainer::LATEST_PATH), '', $fullPath);
            if (is_dir($fullPath) && strpos($dir . DIRECTORY_SEPARATOR . $file, 'install') === false) {
                $list = array_merge($list, $this->listFilesToUpgrade($fullPath));
            }
        }

        return $list;
    }

    /**
     * upgradeThisFile.
     *
     * @param mixed $file
     */
    public function upgradeThisFile($file)
    {
        // translations_custom and mails_custom list are currently not used
        // later, we could handle customization with some kind of diff functions
        // for now, just copy $file in str_replace($this->latestRootDir,_PS_ROOT_DIR_)
        $orig = $this->container->getProperty(UpgradeContainer::LATEST_PATH) . $file;
        $dest = $this->destUpgradePath . $file;

        if ($this->container->getFilesystemAdapter()->isFileSkipped($file, $dest, 'upgrade')) {
            $this->logger->debug($this->translator->trans('%s ignored', [$file], 'Modules.Autoupgrade.Admin'));

            return true;
        }
        if (is_dir($orig)) {
            // if $dest is not a directory (that can happen), just remove that file
            if (!is_dir($dest) && file_exists($dest)) {
                unlink($dest);
                $this->logger->debug($this->translator->trans('[WARNING] File %1$s has been deleted.', [$file], 'Modules.Autoupgrade.Admin'));
            }
            if (!file_exists($dest)) {
                if (mkdir($dest)) {
                    $this->logger->debug($this->translator->trans('Directory %1$s created.', [$file], 'Modules.Autoupgrade.Admin'));

                    return true;
                } else {
                    $this->next = 'error';
                    $this->logger->error($this->translator->trans('Error while creating directory %s.', [$dest], 'Modules.Autoupgrade.Admin'));

                    return false;
                }
            } else { // directory already exists
                $this->logger->debug($this->translator->trans('Directory %s already exists.', [$file], 'Modules.Autoupgrade.Admin'));

                return true;
            }
        } elseif (is_file($orig)) {
            $translationAdapter = $this->container->getTranslationAdapter();
            if ($translationAdapter->isTranslationFile($file) && file_exists($dest)) {
                $type_trad = $translationAdapter->getTranslationFileType($file);
                if ($translationAdapter->mergeTranslationFile($orig, $dest, $type_trad)) {
                    $this->logger->info($this->translator->trans('[TRANSLATION] The translation files have been merged into file %s.', [$dest], 'Modules.Autoupgrade.Admin'));

                    return true;
                }
                $this->logger->warning($this->translator->trans(
                    '[TRANSLATION] The translation files have not been merged into file %filename%. Switch to copy %filename%.',
                    ['%filename%' => $dest],
                    'Modules.Autoupgrade.Admin'
                ));
            }

            // upgrade exception were above. This part now process all files that have to be upgraded (means to modify or to remove)
            // delete before updating (and this will also remove deprecated files)
            if (copy($orig, $dest)) {
                $this->logger->debug($this->translator->trans('Copied %1$s.', [$file], 'Modules.Autoupgrade.Admin'));

                return true;
            } else {
                $this->next = 'error';
                $this->logger->error($this->translator->trans('Error while copying file %s', [$file], 'Modules.Autoupgrade.Admin'));

                return false;
            }
        } elseif (is_file($dest)) {
            if (file_exists($dest)) {
                unlink($dest);
            }
            $this->logger->debug(sprintf('removed file %1$s.', $file));

            return true;
        } elseif (is_dir($dest)) {
            if (strpos($dest, DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR) === false) {
                FilesystemAdapter::deleteDirectory($dest, true);
            }
            $this->logger->debug(sprintf('removed dir %1$s.', $file));

            return true;
        } else {
            return true;
        }
    }

    /**
     * First call of this task needs a warmup, where we load the files list to be upgraded.
     *
     * @return bool
     */
    protected function warmUp()
    {
        $newReleasePath = $this->container->getProperty(UpgradeContainer::LATEST_PATH);
        if (!$this->container->getFilesystemAdapter()->isReleaseValid($newReleasePath)) {
            $this->logger->error($this->translator->trans('Could not assert the folder %s contains a valid PrestaShop release, exiting.', [$newReleasePath], 'Modules.Autoupgrade.Admin'));
            $this->logger->error($this->translator->trans('A file may be missing, or the release is stored in a subfolder by mistake.', [], 'Modules.Autoupgrade.Admin'));
            $this->next = 'error';

            return false;
        }

        $admin_dir = str_replace($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH) . DIRECTORY_SEPARATOR, '', $this->container->getProperty(UpgradeContainer::PS_ADMIN_PATH));
        if (file_exists($newReleasePath . DIRECTORY_SEPARATOR . 'admin')) {
            rename($newReleasePath . DIRECTORY_SEPARATOR . 'admin', $newReleasePath . DIRECTORY_SEPARATOR . $admin_dir);
        } elseif (file_exists($newReleasePath . DIRECTORY_SEPARATOR . 'admin-dev')) {
            rename($newReleasePath . DIRECTORY_SEPARATOR . 'admin-dev', $newReleasePath . DIRECTORY_SEPARATOR . $admin_dir);
        }
        if (file_exists($newReleasePath . DIRECTORY_SEPARATOR . 'install-dev')) {
            rename($newReleasePath . DIRECTORY_SEPARATOR . 'install-dev', $newReleasePath . DIRECTORY_SEPARATOR . 'install');
        }

        // list saved in UpgradeFileNames::toUpgradeFileList
        // get files differences (previously generated)
        $admin_dir = trim(str_replace($this->container->getProperty(UpgradeContainer::PS_ROOT_PATH), '', $this->container->getProperty(UpgradeContainer::PS_ADMIN_PATH)), DIRECTORY_SEPARATOR);
        $filepath_list_diff = $this->container->getProperty(UpgradeContainer::WORKSPACE_PATH) . DIRECTORY_SEPARATOR . UpgradeFileNames::FILES_DIFF_LIST;
        $list_files_diff = [];
        if (file_exists($filepath_list_diff)) {
            $list_files_diff = $this->container->getFileConfigurationStorage()->load(UpgradeFileNames::FILES_DIFF_LIST);
            // only keep list of files to delete. The modified files will be listed with _listFilesToUpgrade
            $list_files_diff = $list_files_diff['deleted'];
            foreach ($list_files_diff as $k => $path) {
                if (preg_match('#autoupgrade#', $path)) {
                    unset($list_files_diff[$k]);
                } else {
                    $list_files_diff[$k] = str_replace('/' . 'admin', '/' . $admin_dir, $path);
                }
            } // do not replace by DIRECTORY_SEPARATOR
        }

        $list_files_to_upgrade = $this->listFilesToUpgrade($newReleasePath);
        if (false === $list_files_to_upgrade) {
            return false;
        }

        // also add files to remove
        $list_files_to_upgrade = array_reverse(array_merge($list_files_diff, $list_files_to_upgrade));

        $filesToMoveToTheEnd = [
            DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php',
            DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'ClassLoader.php',
            DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'autoload_classmap.php',
            DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'autoload_files.php',
            DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'autoload_namespaces.php',
            DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'autoload_psr4.php',
            DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'autoload_real.php',
            DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'autoload_static.php',
            DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'include_paths.php',
            DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer',
            DIRECTORY_SEPARATOR . 'vendor',
        ];

        foreach ($filesToMoveToTheEnd as $file) {
            if ($key = array_search($file, $list_files_to_upgrade)) {
                unset($list_files_to_upgrade[$key]);
                $list_files_to_upgrade[] = $file;
            }
        }

        // save in a serialized array in UpgradeFileNames::toUpgradeFileList
        $this->container->getFileConfigurationStorage()->save($list_files_to_upgrade, UpgradeFileNames::FILES_TO_UPGRADE_LIST);
        $total_files_to_upgrade = count($list_files_to_upgrade);

        if ($total_files_to_upgrade == 0) {
            $this->logger->error($this->translator->trans('[ERROR] Unable to find files to upgrade.', [], 'Modules.Autoupgrade.Admin'));
            $this->next = 'error';

            return false;
        }
        $this->logger->info($this->translator->trans('%s files will be upgraded.', [$total_files_to_upgrade], 'Modules.Autoupgrade.Admin'));
        $this->next = 'upgradeFiles';
        $this->stepDone = false;

        return true;
    }

    public function init()
    {
        // Do nothing. Overrides parent init for avoiding core to be loaded here.
    }
}
