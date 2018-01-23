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

// ToDo: Fix translations placeholders
class Unzipper
{
    private $translator;

    /**
     * @var array logging what happened during the class execution
     */
    private $logs = array();
    
    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }
    
    /**
     * @desc extract a zip file to the given directory
     * @return bool success
     * we need a copy of it to be able to restore without keeping Tools and Autoload stuff
     */
    public function zipExtract($from_file, $to_dir)
    {
        $this->logs = array();
        if (!is_file($from_file)) {
            $this->logs[] = $this->translator->trans('%s is not a file', array($from_file), 'Modules.Autoupgrade.Admin');
            return false;
        }

        if (!file_exists($to_dir)) {
            // ToDo: Use Filesystem from Symfony
            if (!mkdir($to_dir)) {
                $this->logs[] = $this->translator->trans('Unable to create directory %s.', array($to_dir), 'Modules.Autoupgrade.Admin');
                return false;
            }
            chmod($to_dir, 0775);
        }

        $res = false;
        if (!self::$force_pclZip && class_exists('ZipArchive', false)) {
            $res = $this->unZipWithZipArchive($from_file, $to_dir);
        }
        if (!$res) {
            // Fallback. If extracting failed with Zip Archive, we try with PclZip
            $res = $this->unZipWithPclZip($from_file, $to_dir);
        }
        return $res;
    }

    private function unZipWithZipArchive($from_file, $to_dir)
    {
        $this->logs[] = $this->translator->trans('Using class ZipArchive...', array(), 'Modules.Autoupgrade.Admin');
        $zip = new \ZipArchive();
        if ($zip->open($from_file) !== true || empty($zip->filename)) {
            $this->logs[] = $this->translator->trans('Unable to open zipFile %s', array($from_file), 'Modules.Autoupgrade.Admin');
            return false;
        }

        if (!$zip->extractTo($to_dir)) {
            $zip->close();
            $this->logs[] = $this->translator->trans('zip->extractTo(): unable to use %s as extract destination.', array($to_dir), 'Modules.Autoupgrade.Admin');
            return false;
        }

        $zip->close();
        $this->logs[] = $this->translator->trans('Archive extracted', array(), 'Modules.Autoupgrade.Admin');
        return true;
    }

    private function unZipWithPclZip($from_file, $to_dir)
    {
        if (!class_exists('PclZip', false)) {
            require_once(_PS_ROOT_DIR_.'/modules/autoupgrade/classes/pclzip.lib.php');
        }

        $this->logs[] = $this->translator->trans('Using class PclZip...', array(), 'Modules.Autoupgrade.Admin');

        $zip = new \PclZip($from_file);

        if (($file_list = $zip->listContent()) == 0) {
            $this->logs[] = $this->translator->trans('[ERROR] Error on extracting archive using PclZip: %s.', array($zip->errorInfo(true)), 'Modules.Autoupgrade.Admin');
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
                $i++;
            }
        }

        // replace also modified files
        foreach ($indexes as $index) {
            if (($extract_result = $zip->extract(PCLZIP_OPT_BY_INDEX, $index, PCLZIP_OPT_PATH, $to_dir, PCLZIP_OPT_REPLACE_NEWER)) == 0) {
                $this->logs[] = $this->translator->trans('[ERROR] Error on extracting archive using PclZip: %s.', array($zip->errorInfo(true)), 'Modules.Autoupgrade.Admin');
                return false;
            }
            foreach ($extract_result as $extractedFile) {
                $file = str_replace($this->prodRootDir, '', $extractedFile['filename']);
                if ($extractedFile['status'] != 'ok' && $extractedFile['status'] != 'already_a_directory') {
                    $this->logs[] = $this->translation->trans('[ERROR] %file% has not been unzipped: %status%', array('%file%' => $file, '%status%' => $extractedFile['status']), 'Modules.Autoupgrade.Admin');
                    return false;
                }
                $this->logs[] = sprintf('%1$s unzipped into %2$s', $file, str_replace(_PS_ROOT_DIR_, '', $to_dir));
            }
        }
        return true;
    }
}