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

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Miscellaneous;

use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFileNames;
use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Upgrader;

/**
* List the files modified in the current installation regards to the original version
 */
class CheckFilesVersion extends AbstractTask
{
    public function run()
    {
        // do nothing after this request (see javascript function doAjaxRequest )
        $this->upgradeClass->next = '';
        $upgrader = new Upgrader();

        $changedFileList = $upgrader->getChangedFilesList();

        if ($upgrader->isAuthenticPrestashopVersion() === true
            && !is_array($changedFileList)) {
            $this->upgradeClass->nextParams['status'] = 'error';
            $this->upgradeClass->nextParams['msg'] = $this->upgradeClass->getTranslator()->trans('Unable to check files for the installed version of PrestaShop.', array(), 'Modules.Autoupgrade.Admin');
            $testOrigCore = false;
        } else {
            if ($upgrader->isAuthenticPrestashopVersion() === true) {
                $this->upgradeClass->nextParams['status'] = 'ok';
                $testOrigCore = true;
            } else {
                $testOrigCore = false;
                $this->upgradeClass->nextParams['status'] = 'warn';
            }

            if (!isset($changedFileList['core'])) {
                $changedFileList['core'] = array();
            }

            if (!isset($changedFileList['translation'])) {
                $changedFileList['translation'] = array();
            }
            $this->upgradeClass->getFileConfigurationStorage()->save($changedFileList['translation'], UpgradeFileNames::tradCustomList);

            if (!isset($changedFileList['mail'])) {
                $changedFileList['mail'] = array();
            }
            $this->upgradeClass->getFileConfigurationStorage()->save($changedFileList['mail'], UpgradeFileNames::mailCustomList);


            if ($changedFileList === false) {
                $changedFileList = array();
                $this->upgradeClass->nextParams['msg'] = $this->upgradeClass->getTranslator()->trans('Unable to check files', array(), 'Modules.Autoupgrade.Admin');
                $this->upgradeClass->nextParams['status'] = 'error';
            } else {
                $this->upgradeClass->nextParams['msg'] = ($testOrigCore ? $this->upgradeClass->getTranslator()->trans('Core files are ok', array(), 'Modules.Autoupgrade.Admin') : $this->upgradeClass->getTranslator()->trans(
                    '%modificationscount% file modifications have been detected, including %coremodifications% from core and native modules:',
                    array(
                        '%modificationscount%' => count(array_merge($changedFileList['core'], $changedFileList['mail'], $changedFileList['translation'])),
                        '%coremodifications%' => count($changedFileList['core']),
                    ),
                    'Modules.Autoupgrade.Admin')
                );
            }
            $this->upgradeClass->nextParams['result'] = $changedFileList;
        }
    }
}