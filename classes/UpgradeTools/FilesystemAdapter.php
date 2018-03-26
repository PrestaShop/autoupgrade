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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use PrestaShop\Module\AutoUpgrade\Tools14;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FileFilter;

class FilesystemAdapter
{
    private $restoreFilesFilename;

    private $fileFilter;

    private $autoupgradeDir;
    private $adminSubDir;
    private $prodRootDir;

    public function __construct(FileFilter $fileFilter, $restoreFilesFilename,
        $autoupgradeDir, $adminSubDir, $prodRootDir)
    {
        $this->fileFilter = $fileFilter;
        $this->restoreFilesFilename = $restoreFilesFilename;

        $this->autoupgradeDir = $autoupgradeDir;
        $this->adminSubDir = $adminSubDir;
        $this->prodRootDir = $prodRootDir;
    }

    /**
     * Delete directory and subdirectories
     *
     * @param string $dirname Directory name
     */
    public static function deleteDirectory($dirname, $delete_self = true)
    {
        return Tools14::deleteDirectory($dirname, $delete_self);
    }

    public function listFilesInDir($dir, $way = 'backup', $list_directories = false)
    {
        $list = array();
        $dir = rtrim($dir, '/').DIRECTORY_SEPARATOR;
        $allFiles = false;
        if (is_dir($dir) && is_readable($dir)) {
            $allFiles = scandir($dir);
        }
        if (!is_array($allFiles)) {
            return $list;
        }
        foreach ($allFiles as $file) {
            $fullPath = $dir.$file;
            if ($file[0] == '.' || $this->isFileSkipped($file, $fullPath, $way)) {
                continue;
            }
            if (is_dir($fullPath)) {
                $list = array_merge($list, $this->listFilesInDir($fullPath, $way, $list_directories));
                if ($list_directories) {
                    $list[] = $fullPath;
                }
            } else {
                $list[] = $fullPath;
            }
        }
        return $list;
    }

    /**
     * this function list all files that will be remove to retrieve the filesystem states before the upgrade
     *
     * @access public
     * @return array of files to delete
     */
    public function listFilesToRemove()
    {
        $prev_version = preg_match('#auto-backupfiles_V([0-9.]*)_#', $this->restoreFilesFilename, $matches);
        if ($prev_version) {
            $prev_version = $matches[1];
        }

        $toRemove = false;
        // note : getDiffFilesList does not include files moved by upgrade scripts,
        // so this method can't be trusted to fully restore directory
        // $toRemove = $this->upgrader->getDiffFilesList(_PS_VERSION_, $prev_version, false);
        // if we can't find the diff file list corresponding to _PS_VERSION_ and prev_version,
        // let's assume to remove every files
        if (!$toRemove) {
            $toRemove = $this->listFilesInDir($this->prodRootDir, 'restore', true);
        }

        // if a file in "ToRemove" has been skipped during backup,
        // just keep it
        foreach ($toRemove as $key => $file) {
            $filename = substr($file, strrpos($file, '/')+1);
            $toRemove[$key] = preg_replace('#^/admin#', $this->adminSubDir, $file);
            // this is a really sensitive part, so we add an extra checks: preserve everything that contains "autoupgrade"
            if ($this->isFileSkipped($filename, $file, 'backup') || strpos($file, $this->autoupgradeDir)) {
                unset($toRemove[$key]);
            }
        }
        return $toRemove;
    }

    /**
     * listSampleFiles will make a recursive call to scandir() function
     * and list all file which match to the $fileext suffixe (this can be an extension or whole filename)
     *
     * @param string $dir directory to look in
     * @param string $fileext suffixe filename
     * @return array of files
     */
    public function listSampleFiles($dir, $fileext = '.jpg')
    {
        $res = array();
        if (is_array($dir)) {
            foreach ($dir as $singleDir) {
                $res = array_merge($res, $this->listSampleFiles($singleDir, $fileext));
            }
            return $res;
        }

        $dir = rtrim($dir, '/').DIRECTORY_SEPARATOR;
        $toDel = false;
        if (is_dir($dir) && is_readable($dir)) {
            $toDel = scandir($dir);
        }
        // copied (and kind of) adapted from AdminImages.php
        if (is_array($toDel)) {
            foreach ($toDel as $file) {
                if ($file[0] != '.') {
                    if (preg_match('#'.preg_quote($fileext, '#').'$#i', $file)) {
                        $res[] = $dir.$file;
                    } elseif (is_dir($dir.$file)) {
                        $res = array_merge($res, $this->listSampleFiles($dir.$file, $fileext));
                    }
                }
            }
        }
        return $res;
    }
    
    /**
     *	bool _skipFile : check whether a file is in backup or restore skip list
     *
     * @param type $file : current file or directory name eg:'.svn' , 'settings.inc.php'
     * @param type $fullpath : current file or directory fullpath eg:'/home/web/www/prestashop/app/config/parameters.php'
     * @param type $way : 'backup' , 'upgrade'
     */
    public function isFileSkipped($file, $fullpath, $way = 'backup')
    {
        $fullpath = str_replace('\\', '/', $fullpath); // wamp compliant
        $rootpath = str_replace('\\', '/', $this->prodRootDir);
        switch ($way) {
            case 'backup':
                if (in_array($file, $this->fileFilter->getExcludeFiles())) {
                    return true;
                }

                foreach ($this->fileFilter->getFilesToIgnoreOnBackup() as $path) {
                    $path = str_replace(DIRECTORY_SEPARATOR.'admin', DIRECTORY_SEPARATOR.$this->adminSubDir, $path);
                    if ($fullpath == $rootpath.$path) {
                        return true;
                    }
                }
                break;
                // restore or upgrade way : ignore the same files
                // note the restore process use skipFiles only if xml md5 files
                // are unavailable
            case 'restore':
                if (in_array($file, $this->fileFilter->getExcludeFiles())) {
                    return true;
                }

                foreach ($this->fileFilter->getFilesToIgnoreOnRestore() as $path) {
                    $path = str_replace(DIRECTORY_SEPARATOR.'admin', DIRECTORY_SEPARATOR.$this->adminSubDir, $path);
                    if ($fullpath == $rootpath.$path) {
                        return true;
                    }
                }
                break;
            case 'upgrade':
                if (in_array($file, $this->fileFilter->getExcludeFiles())) {
                    return true;
                }

                foreach ($this->fileFilter->getFilesToIgnoreOnUpgrade() as $path) {
                    $path = str_replace(DIRECTORY_SEPARATOR.'admin', DIRECTORY_SEPARATOR.$this->adminSubDir, $path);
                    if (strpos($fullpath, $rootpath.$path) !== false) {
                        return true;
                    }
                }

                break;
                // default : if it's not a backup or an upgrade, do not skip the file
            default:
                return false;
        }
        // by default, don't skip
        return false;
    }
}