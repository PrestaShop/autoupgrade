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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade;

use PrestaShop\Module\AutoUpgrade\Upgrader;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;

/**
 * Download PrestaShop archive according to the chosen channel
 */
class Download extends AbstractTask
{
    public function run()
    {
        if (!\ConfigurationTest::test_fopen() && !\ConfigurationTest::test_curl()) {
            $this->upgradeClass->nextQuickInfo[] = 
            $this->upgradeClass->nextErrors[] = 
            $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('You need allow_url_fopen or cURL enabled for automatic download to work. You can also manually upload it in filepath %s.', array($this->upgradeClass->getFilePath()), 'Modules.Autoupgrade.Admin');
            $this->upgradeClass->next = 'error';
            return;
        }

        if (!is_object($this->upgradeClass->upgrader)) {
            $this->upgradeClass->upgrader = new Upgrader();
        }
        // regex optimization
        preg_match('#([0-9]+\.[0-9]+)(?:\.[0-9]+){1,2}#', _PS_VERSION_, $matches);
        $this->upgradeClass->upgrader->channel = $this->upgradeClass->upgradeConfiguration->get('channel');
        $this->upgradeClass->upgrader->branch = $matches[1];
        if ($this->upgradeClass->upgradeConfiguration->get('channel') == 'private' && !$this->upgradeClass->upgradeConfiguration->get('private_allow_major')) {
            $this->upgradeClass->upgrader->checkPSVersion(false, array('private', 'minor'));
        } else {
            $this->upgradeClass->upgrader->checkPSVersion(false, array('minor'));
        }

        if ($this->upgradeClass->upgrader->channel == 'private') {
            $this->upgradeClass->upgrader->link = $this->upgradeClass->upgradeConfiguration->get('private_release_link');
            $this->upgradeClass->upgrader->md5 = $this->upgradeClass->upgradeConfiguration->get('private_release_md5');
        }
        $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('Downloading from %s', array($this->upgradeClass->upgrader->link), 'Modules.Autoupgrade.Admin');
        $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('File will be saved in %s', array($this->upgradeClass->getFilePath()), 'Modules.Autoupgrade.Admin');
        if (file_exists($this->upgradeClass->downloadPath)) {
            \AdminSelfUpgrade::deleteDirectory($this->upgradeClass->downloadPath, false);
            $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('Download directory has been emptied', array(), 'Modules.Autoupgrade.Admin');
        }
        $report = '';
        $relative_download_path = str_replace(_PS_ROOT_DIR_, '', $this->upgradeClass->downloadPath);
        if (\ConfigurationTest::test_dir($relative_download_path, false, $report)) {
            $res = $this->upgradeClass->upgrader->downloadLast($this->upgradeClass->downloadPath, $this->upgradeClass->destDownloadFilename);
            if ($res) {
                $md5file = md5_file(realpath($this->upgradeClass->downloadPath).DIRECTORY_SEPARATOR.$this->upgradeClass->destDownloadFilename);
                if ($md5file == $this->upgradeClass->upgrader->md5) {
                    $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('Download complete.', array(), 'Modules.Autoupgrade.Admin');
                    $this->upgradeClass->next = 'unzip';
                    $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('Download complete. Now extracting...', array(), 'Modules.Autoupgrade.Admin');
                } else {
                    $this->upgradeClass->nextQuickInfo[] = 
                    $this->upgradeClass->nextErrors[] = $this->upgradeClass->getTranslator()->trans('Download complete but MD5 sum does not match (%s).', array($md5file), 'Modules.Autoupgrade.Admin');
                    $this->upgradeClass->next = 'error';
                    $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('Download complete but MD5 sum does not match (%s). Operation aborted.', array(), 'Modules.Autoupgrade.Admin');
                }
            } else {
                if ($this->upgradeClass->upgrader->channel == 'private') {
                    $this->upgradeClass->next_desc = 
                    $this->upgradeClass->nextQuickInfo[] = 
                    $this->upgradeClass->nextErrors[] = $this->upgradeClass->getTranslator()->trans('Error during download. The private key may be incorrect.', array(), 'Modules.Autoupgrade.Admin');
                } else {
                    $this->upgradeClass->next_desc = 
                    $this->upgradeClass->nextQuickInfo[] = 
                    $this->upgradeClass->nextErrors[] = $this->upgradeClass->getTranslator()->trans('Error during download', array(), 'Modules.Autoupgrade.Admin');
                }
                $this->upgradeClass->next = 'error';
            }
        } else {
            $this->upgradeClass->next_desc = 
            $this->upgradeClass->nextQuickInfo[] = 
            $this->upgradeClass->nextErrors[] = $this->upgradeClass->getTranslator()->trans('Download directory %s is not writable.', array($this->upgradeClass->downloadPath), 'Modules.Autoupgrade.Admin');
            $this->upgradeClass->next = 'error';
        }
    }
}