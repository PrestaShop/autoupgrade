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

namespace PrestaShop\Module\AutoUpgrade;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;

// ToDo: Fix translations placeholders
class ZipAction
{
    // Number of files added in a zip per request
    private $configMaxNbFilesCompressedInARow;
    // Max file size allowed in a zip file
    private $configMaxFileSizeAllowed;

    private $logger;
    private $translator;
    /**
     * @var string Path to the shop, in order to remove it from the archived file paths
     */
    private $prodRootDir;

    /**
     * if set to true, will use pclZip library
     * even if ZipArchive is available.
     */
    const FORCE_PCLZIP = false;

    public function __construct($translator, LoggerInterface $logger, UpgradeConfiguration $configuration, $prodRootDir)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->prodRootDir = $prodRootDir;

        $this->configMaxNbFilesCompressedInARow = $configuration->getNumberOfFilesPerCall();
        $this->configMaxFileSizeAllowed = $configuration->getMaxFileToBackup();
    }

    /**
     * ToDo: function to rename / move ?
     */
    public function compress(&$filesList, $toFile)
    {
        return $this->compressWithZipArchive($filesList, $toFile)
            || $this->compressWithPclZip($filesList, $toFile);
    }

    /**
     * @desc extract a zip file to the given directory
     *
     * @return bool success
     *              we need a copy of it to be able to restore without keeping Tools and Autoload stuff
     */
    public function extract($from_file, $to_dir)
    {
        if (!is_file($from_file)) {
            $this->logger->error($this->translator->trans('%s is not a file', array($from_file), 'Modules.Autoupgrade.Admin'));

            return false;
        }

        if (!file_exists($to_dir)) {
            // ToDo: Use Filesystem from Symfony
            if (!mkdir($to_dir)) {
                $this->logger->error($this->translator->trans('Unable to create directory %s.', array($to_dir), 'Modules.Autoupgrade.Admin'));

                return false;
            }
            chmod($to_dir, 0775);
        }

        if (!$this->extractWithZipArchive($from_file, $to_dir)) {
            // Fallback. If extracting failed with Zip Archive, we try with PclZip
            return $this->extractWithPclZip($from_file, $to_dir);
        }

        return true;
    }

    public function listContent($zipfile)
    {
        if (!file_exists($zipfile)) {
            return array();
        }
        $res = $this->listWithZipArchive($zipfile);
        if (is_array($res)) {
            return $res;
        }
        $resPcl = $this->listWithPclZip($zipfile);
        if (is_array($resPcl)) {
            return $resPcl;
        }
        $this->logger->error($this->translator->trans('[ERROR] Unable to list archived files', array(), 'Modules.Autoupgrade.Admin'));

        return array();
    }

    private function compressWithZipArchive(&$filesList, $toFile)
    {
        $zip = $this->openWithZipArchive($toFile, \ZipArchive::CREATE);
        if ($zip === false) {
            return false;
        }

        for ($i = 0; $i < $this->configMaxNbFilesCompressedInARow && count($filesList); ++$i) {
            $file = array_shift($filesList);

            $archiveFilename = $this->getFilepathInArchive($file);
            if (!$this->isFileWithinFileSizeLimit($file)) {
                continue;
            }

            if (!$zip->addFile($file, $archiveFilename)) {
                // if an error occur, it's more safe to delete the corrupted backup
                $zip->close();
                (new Filesystem())->remove($toFile);
                $this->logger->error($this->translator->trans(
                    'Error when trying to add %filename% to archive %archive%.',
                    array(
                        '%filename%' => $file,
                        '%archive%' => $archiveFilename,
                    ),
                    'Modules.Autoupgrade.Admin'
                ));

                return false;
            }

            $this->logger->debug($this->translator->trans(
                '%filename% added to archive. %filescount% files left.',
                array(
                    '%filename%' => $archiveFilename,
                    '%filescount%' => count($filesList),
                ),
                'Modules.Autoupgrade.Admin'
            ));
        }

        if (!$zip->close()) {
            $this->logger->error($this->translator->trans(
                'Could not close the Zip file properly. Check you are allowed to write on the disk and there is available space on it.',
                array(),
                'Modules.Autoupgrade.Admin'
            ));

            return false;
        }

        return true;
    }

    private function compressWithPclZip(&$filesList, $toFile)
    {
        $zip = $this->openWithPclZip($toFile);
        if (!$zip) {
            return false;
        }

        $files_to_add = array();
        for ($i = 0; $i < $this->configMaxNbFilesCompressedInARow && count($filesList); ++$i) {
            $file = array_shift($filesList);

            $archiveFilename = $this->getFilepathInArchive($file);
            if (!$this->isFileWithinFileSizeLimit($file)) {
                continue;
            }

            $files_to_add[] = $file;
            $this->logger->debug($this->translator->trans(
                '%filename% added to archive. %filescount% files left.',
                array(
                    '%filename%' => $archiveFilename,
                    '%filescount%' => count($filesList),
                ),
                'Modules.Autoupgrade.Admin'
            ));
        }

        $added_to_zip = $zip->add($files_to_add, PCLZIP_OPT_REMOVE_PATH, $this->prodRootDir);
        $zip->privCloseFd();
        if (!$added_to_zip) {
            (new Filesystem())->remove($toFile);
            $this->logger->error($this->translator->trans('[ERROR] Error on backup using PclZip: %s.', array($zip->errorInfo(true)), 'Modules.Autoupgrade.Admin'));

            return false;
        }

        return true;
    }

    private function extractWithZipArchive($from_file, $to_dir)
    {
        $zip = $this->openWithZipArchive($from_file);
        if ($zip === false) {
            return false;
        }

        if (!$zip->extractTo($to_dir)) {
            $zip->close();
            $this->logger->error($this->translator->trans('zip->extractTo(): unable to use %s as extract destination.', array($to_dir), 'Modules.Autoupgrade.Admin'));

            return false;
        }

        $zip->close();
        $this->logger->debug($this->translator->trans('Archive extracted', array(), 'Modules.Autoupgrade.Admin'));

        return true;
    }

    private function extractWithPclZip($fromFile, $toDir)
    {
        $zip = $this->openWithPclZip($fromFile);
        if (!$zip) {
            return false;
        }

        if (($file_list = $zip->listContent()) == 0) {
            $this->logger->error($this->translator->trans('[ERROR] Error on extracting archive using PclZip: %s.', array($zip->errorInfo(true)), 'Modules.Autoupgrade.Admin'));

            return false;
        }

        // PCL is very slow, so we need to extract files 500 by 500
        $i = 0;
        $j = 1;
        $indexes = array();
        foreach ($file_list as $file) {
            if (!isset($indexes[$i])) {
                $indexes[$i] = array();
            }
            $indexes[$i][] = $file['index'];
            if ($j++ % 500 == 0) {
                ++$i;
            }
        }

        // replace also modified files
        foreach ($indexes as $index) {
            if (($extract_result = $zip->extract(PCLZIP_OPT_BY_INDEX, $index, PCLZIP_OPT_PATH, $toDir, PCLZIP_OPT_REPLACE_NEWER)) == 0) {
                $this->logger->error($this->translator->trans('[ERROR] Error on extracting archive using PclZip: %s.', array($zip->errorInfo(true)), 'Modules.Autoupgrade.Admin'));

                return false;
            }
            foreach ($extract_result as $extractedFile) {
                $file = str_replace($this->prodRootDir, '', $extractedFile['filename']);
                if ($extractedFile['status'] != 'ok' && $extractedFile['status'] != 'already_a_directory') {
                    $this->logger->error($this->translator->trans('[ERROR] %file% has not been unzipped: %status%', array('%file%' => $file, '%status%' => $extractedFile['status']), 'Modules.Autoupgrade.Admin'));

                    return false;
                }
                $this->logger->debug(sprintf('%1$s unzipped into %2$s', $file, str_replace(_PS_ROOT_DIR_, '', $toDir)));
            }
        }

        return true;
    }

    private function getFilepathInArchive($filepath)
    {
        return ltrim(str_replace($this->prodRootDir, '', $filepath), DIRECTORY_SEPARATOR);
    }

    private function isFileWithinFileSizeLimit($filepath)
    {
        $size = filesize($filepath);
        $pass = ($size < $this->configMaxFileSizeAllowed);
        if (!$pass) {
            $this->logger->debug($this->translator->trans(
                'File %filename% (size: %filesize%) has been skipped during backup.',
                array(
                    '%filename%' => $this->getFilepathInArchive($filepath),
                    '%filesize%' => $size,
                ),
                'Modules.Autoupgrade.Admin'
            ));
        }

        return $pass;
    }

    private function listWithZipArchive($zipfile)
    {
        $zip = $this->openWithZipArchive($zipfile);
        if ($zip === false) {
            return false;
        }

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $files[] = $zip->getNameIndex($i);
        }

        return $files;
    }

    private function listWithPclZip($zipfile)
    {
        $zip = $this->openWithPclZip($zipFile);
        if ($zip !== false) {
            return false;
        }

        return $zip->listContent();
    }

    private function openWithZipArchive($zipFile, $flags = null)
    {
        if (self::FORCE_PCLZIP || !class_exists('ZipArchive', false)) {
            return false;
        }

        $this->logger->debug($this->translator->trans('Using class ZipArchive...', array(), 'Modules.Autoupgrade.Admin'));
        $zip = new \ZipArchive();
        if ($zip->open($zipFile, $flags) !== true || empty($zip->filename)) {
            $this->logger->error($this->translator->trans('Unable to open zipFile %s', array($zipFile), 'Modules.Autoupgrade.Admin'));

            return false;
        }

        return $zip;
    }

    private function openWithPclZip($zipFile)
    {
        if (!class_exists('PclZip', false)) {
            require_once _PS_ROOT_DIR_ . '/modules/autoupgrade/classes/pclzip.lib.php';
        }

        $this->logger->debug($this->translator->trans('Using class PclZip...', array(), 'Modules.Autoupgrade.Admin'));
        $zip = new \PclZip($zipFile);
        if (!$zip) {
            $this->logger->error($this->translator->trans('Unable to open archive', array(), 'Modules.Autoupgrade.Admin'));
        }

        return $zip;
    }
}
