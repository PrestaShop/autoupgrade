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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use FilesystemIterator;
use PrestaShop\Module\AutoUpgrade\Tools14;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FilesystemAdapter
{
    /**
     * @var FileFilter
     */
    private $fileFilter;

    /**
     * @var string
     */
    private $autoupgradeDir;

    /**
     * @var string
     */
    private $adminSubDir;

    /**
     * @var string
     */
    private $prodRootDir;

    /**
     * Somes elements to find in a folder.
     * If one of them cannot be found, we can consider that the release is invalid.
     *
     * @var array<string, array<string>>
     */
    private $releaseFileChecks = [
        'files' => [
            'index.php',
            'config/defines.inc.php',
        ],
        'folders' => [
            'classes',
            'controllers',
        ],
    ];

    public function __construct(
        FileFilter $fileFilter,
        string $autoupgradeDir,
        string $adminSubDir,
        string $prodRootDir
    ) {
        $this->fileFilter = $fileFilter;

        $this->autoupgradeDir = $autoupgradeDir;
        $this->adminSubDir = $adminSubDir;
        $this->prodRootDir = $prodRootDir;
    }

    /**
     * Delete directory and subdirectories.
     *
     * @param string $dirname Directory name
     */
    public static function deleteDirectory(string $dirname, bool $delete_self = true): bool
    {
        return Tools14::deleteDirectory($dirname, $delete_self);
    }

    /**
     * @param 'upgrade'|'restore'|'backup' $way
     *
     * @return string[]
     */
    public function listFilesInDir(string $dir, string $way, bool $listDirectories = false): array
    {
        $files = [];
        $directory = new RecursiveDirectoryIterator(
            $dir,
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::KEY_AS_FILENAME | FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::UNIX_PATHS
        );
        $filter = new RecursiveCallbackFilterIterator($directory, function ($current, $key, $iterator) use ($way, $dir) {
            return !$this->isFileSkipped($key, $current, $way, $dir);
        });
        $iterator = new \RecursiveIteratorIterator(
            $filter,
            $listDirectories ? RecursiveIteratorIterator::SELF_FIRST : RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $info) {
            $files[] = $info;
        }

        return $files;
    }

    /**
     * this function list all files that will be remove to retrieve the filesystem states before the upgrade.
     *
     * @return string[] of files to delete
     */
    public function listFilesToRemove(): array
    {
        // if we can't find the diff file list corresponding to _PS_VERSION_ and prev_version,
        // let's assume to remove every files
        $toRemove = $this->listFilesInDir($this->prodRootDir, 'restore', true);

        // if a file in "ToRemove" has been skipped during backup,
        // just keep it
        foreach ($toRemove as $key => $file) {
            $filename = substr($file, strrpos($file, '/') + 1);
            $toRemove[$key] = preg_replace('#^/admin#', $this->adminSubDir, $file);
            // this is a really sensitive part, so we add an extra checks: preserve everything that contains "autoupgrade"
            if ($this->isFileSkipped($filename, $file) || strpos($file, $this->autoupgradeDir)) {
                unset($toRemove[$key]);
            }
        }

        return $toRemove;
    }

    /**
     * listSampleFiles will make a recursive call to scandir() function
     * and list all file which match to the $fileext suffixe (this can be an extension or whole filename).
     *
     * @param string $dir directory to look in
     * @param string $fileext suffixe filename
     *
     * @return string[] of files
     */
    public function listSampleFiles(string $dir, string $fileext = '.jpg'): array
    {
        $res = [];
        $dir = rtrim($dir, '/') . DIRECTORY_SEPARATOR;
        $toDel = false;
        if (is_dir($dir) && is_readable($dir)) {
            $toDel = scandir($dir);
        }
        // copied (and kind of) adapted from AdminImages.php
        if (is_array($toDel)) {
            foreach ($toDel as $file) {
                if ($file[0] != '.') {
                    if (preg_match('#' . preg_quote($fileext, '#') . '$#i', $file)) {
                        $res[] = $dir . $file;
                    } elseif (is_dir($dir . $file)) {
                        $res = array_merge($res, $this->listSampleFiles($dir . $file, $fileext));
                    }
                }
            }
        }

        return $res;
    }

    /**
     * @param string $file : current file or directory name eg:'.svn' , 'settings.inc.php'
     * @param string $fullpath : current file or directory fullpath eg:'/home/web/www/prestashop/app/config/parameters.php'
     * @param 'upgrade'|'restore'|'backup' $way
     * @param string|null $temporaryWorkspace : If needed, another folder than the shop root can be used (used for releases)
     *
     * @return bool
     */
    public function isFileSkipped(string $file, string $fullpath, string $way = 'backup', string $temporaryWorkspace = null): bool
    {
        $fullpath = str_replace('\\', '/', $fullpath); // wamp compliant
        $rootpath = str_replace(
            '\\',
            '/',
            (null !== $temporaryWorkspace) ? $temporaryWorkspace : $this->prodRootDir
        );

        if (in_array($file, $this->fileFilter->getExcludeFiles())) {
            return true;
        }

        $ignoreList = [];
        if ('backup' === $way) {
            $ignoreList = $this->fileFilter->getFilesToIgnoreOnBackup();
        } elseif ('restore' === $way) {
            $ignoreList = $this->fileFilter->getFilesToIgnoreOnRestore();
        } elseif ('upgrade' === $way) {
            $ignoreList = $this->fileFilter->getFilesToIgnoreOnUpgrade();
        }

        foreach ($ignoreList as $path) {
            $path = str_replace(DIRECTORY_SEPARATOR . 'admin', DIRECTORY_SEPARATOR . $this->adminSubDir, $path);
            if (strpos($fullpath, $rootpath . $path) === 0 && /* endsWith */ substr($fullpath, -strlen($rootpath . $path)) === $rootpath . $path) {
                return true;
            }
            if (strpos($path, '*') !== false && fnmatch($rootpath . $path, $fullpath, FNM_PATHNAME)) {
                return true;
            }
        }

        // by default, don't skip
        return false;
    }

    /**
     * Check a directory has some files available in every release of PrestaShop.
     *
     * @param string $path Workspace to check
     *
     * @return bool
     */
    public function isReleaseValid(string $path): bool
    {
        foreach ($this->releaseFileChecks as $type => $elements) {
            foreach ($elements as $element) {
                $fullPath = $path . DIRECTORY_SEPARATOR . $element;
                if ('files' === $type && !is_file($fullPath)) {
                    return false;
                }
                if ('folders' === $type && !is_dir($fullPath)) {
                    return false;
                }
            }
        }

        return true;
    }
}
