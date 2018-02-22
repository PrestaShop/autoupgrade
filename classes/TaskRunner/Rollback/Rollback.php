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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Rollback;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFiles;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;

/**
 * First step executed during a rollback
 */
class Rollback extends AbstractTask
{
    public function run()
    {
        // 1st, need to analyse what was wrong.
        $this->upgradeClass->nextParams = $this->upgradeClass->currentParams;
        $restoreName = $this->upgradeClass->getState()-> getRestoreName();
        $this->upgradeClass->getState()-> setRestoreFilesFilename($restoreName);
        $restoreDbFilenames = $this->upgradeClass->getState()-> getRestoreDbFilenames();

        if (empty($restoreName)) {
            $this->upgradeClass->next = 'noRollbackFound';
            return;
        }

        $files = scandir($this->upgradeClass->backupPath);
        // find backup filenames, and be sure they exists
        foreach ($files as $file) {
            if (preg_match('#'.preg_quote('auto-backupfiles_'.$restoreName).'#', $file)) {
                $this->upgradeClass->getState()-> setRestoreFilesFilename($file);
                break;
            }
        }
        if (!is_file($this->upgradeClass->backupPath.DIRECTORY_SEPARATOR.$this->upgradeClass->getState()-> getRestoreFilesFilename())) {
            $this->upgradeClass->next = 'error';
            $this->upgradeClass->nextQuickInfo[] = 
            $this->upgradeClass->nextErrors[] = 
            $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('[ERROR] File %s is missing: unable to restore files. Operation aborted.', array($this->upgradeClass->getState()-> getRestoreFilesFilename()), 'Modules.Autoupgrade.Admin');
            return false;
        }
        $files = scandir($this->upgradeClass->backupPath.DIRECTORY_SEPARATOR.$restoreName);
        foreach ($files as $file) {
            if (preg_match('#auto-backupdb_[0-9]{6}_'.preg_quote($restoreName).'#', $file)) {
                $restoreDbFilenames[] = $file;
            }
        }


        // order files is important !
        sort($restoreDbFilenames);
        $this->upgradeClass->getState()-> setRestoreDbFilenames($restoreDbFilenames);
        if (count($restoreDbFilenames) == 0) {
            $this->upgradeClass->next = 'error';
            $this->upgradeClass->nextQuickInfo[] =
            $this->upgradeClass->nextErrors[] =
            $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('[ERROR] No backup database files found: it would be impossible to restore the database. Operation aborted.', array(), 'Modules.Autoupgrade.Admin');
            return false;
        }

        $this->upgradeClass->next = 'restoreFiles';
        $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('Restoring files ...', array(), 'Modules.Autoupgrade.Admin');
        // remove tmp files related to restoreFiles
        if (file_exists($this->upgradeClass->autoupgradePath.DIRECTORY_SEPARATOR.UpgradeFiles::fromArchiveFileList)) {
            unlink($this->upgradeClass->autoupgradePath.DIRECTORY_SEPARATOR.UpgradeFiles::fromArchiveFileList);
        }
        if (file_exists($this->upgradeClass->autoupgradePath.DIRECTORY_SEPARATOR.UpgradeFiles::toRemoveFileList)) {
            unlink($this->upgradeClass->autoupgradePath.DIRECTORY_SEPARATOR.UpgradeFiles::toRemoveFileList);
        }
    }
}