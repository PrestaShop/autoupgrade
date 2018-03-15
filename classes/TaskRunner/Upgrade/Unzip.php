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

use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;

/**
* extract chosen version into $this->upgradeClass->latestPath directory
*/
class Unzip extends AbstractTask
{
    public function run()
    {
        $filepath = $this->upgradeClass->getFilePath();
        $destExtract = $this->upgradeClass->latestPath;

        if (file_exists($destExtract)) {
            \AdminSelfUpgrade::deleteDirectory($destExtract, false);
            $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('"/latest" directory has been emptied', array(), 'Modules.Autoupgrade.Admin');
        }
        $relative_extract_path = str_replace(_PS_ROOT_DIR_, '', $destExtract);
        $report = '';
        if (!\ConfigurationTest::test_dir($relative_extract_path, false, $report)) {
            $this->logger->error($this->upgradeClass->getTranslator()->trans('Extraction directory %s is not writable.', array($destExtract), 'Modules.Autoupgrade.Admin'));
            $this->upgradeClass->next = 'error';
            $this->upgradeClass->error = true;
            return false;
        }

        $res = $this->upgradeClass->getZipAction()->extract($filepath, $destExtract);
        $this->upgradeClass->nextQuickInfo = array_merge($this->upgradeClass->nextQuickInfo, $this->upgradeClass->getZipAction()->getLogs());

        if (!$res) {
            $this->upgradeClass->next = 'error';
            $this->upgradeClass->error= true;
            $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans(
                'Unable to extract %filepath% file into %destination% folder...',
                array(
                    '%filepath%' => $filepath,
                    '%destination%' => $destExtract,
                ),
                'Modules.Autoupgrade.Admin'
            );
            return false;
        }

        // new system release archive
        $newZip = $destExtract.DIRECTORY_SEPARATOR.'prestashop.zip';
        // ToDo : only 1.7!!!
        if (!is_file($newZip)) {
            $this->upgradeClass->next = 'error';
            $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('This is not a valid archive for version %s.', array(INSTALL_VERSION), 'Modules.Autoupgrade.Admin');
            return false;
        }

        @unlink($destExtract.DIRECTORY_SEPARATOR.'/index.php');
        @unlink($destExtract.DIRECTORY_SEPARATOR.'/Install_PrestaShop.html');

        $subRes = $this->upgradeClass->getZipAction()->extract($newZip, $destExtract);
        $this->upgradeClass->nextQuickInfo = array_merge($this->upgradeClass->nextQuickInfo, $this->upgradeClass->getZipAction()->getLogs());
        if (!$subRes) {
            $this->upgradeClass->next = 'error';
            $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans(
                'Unable to extract %filepath% file into %destination% folder...',
                array(
                    '%filepath%' => $filepath,
                    '%destination%' => $destExtract,
                ),
                'Modules.Autoupgrade.Admin'
            );
            return false;
        }

        // Unsetting to force listing
        unset($this->upgradeClass->nextParams['removeList']);
        $this->upgradeClass->next = 'removeSamples';
        $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('File extraction complete. Removing sample files...', array(), 'Modules.Autoupgrade.Admin');

        @unlink($newZip);

        return true;
    }
}