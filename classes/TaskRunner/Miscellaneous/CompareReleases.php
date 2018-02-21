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

use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeFiles;
use PrestaShop\Module\AutoUpgrade\Upgrader;

/**
* get the list of all modified and deleted files between current version
* and target version (according to channel configuration)
*/
class CompareReleases extends AbstractTask
{
    public function run()
    {
        // do nothing after this request (see javascript function doAjaxRequest )
        $this->upgradeClass->next = '';
        $channel = $this->upgradeClass->getUpgradeConfiguration()->get('channel');
        $upgrader = new Upgrader();
        switch ($channel) {
            case 'archive':
                $version = $this->upgradeClass->getUpgradeConfiguration()->get('archive.version_num');
                break;
            case 'directory':
                $version = $this->upgradeClass->getUpgradeConfiguration()->get('directory.version_num');
                break;
            default:
                preg_match('#([0-9]+\.[0-9]+)(?:\.[0-9]+){1,2}#', _PS_VERSION_, $matches);
                $upgrader->branch = $matches[1];
                $upgrader->channel = $channel;
                if ($this->upgradeClass->getUpgradeConfiguration()->get('channel') == 'private' && !$this->upgradeClass->getUpgradeConfiguration()->get('private_allow_major')) {
                    $upgrader->checkPSVersion(false, array('private', 'minor'));
                } else {
                    $upgrader->checkPSVersion(false, array('minor'));
                }
                $version = $upgrader->version_num;
        }

        $diffFileList = $upgrader->getDiffFilesList(_PS_VERSION_, $version);
        if (!is_array($diffFileList)) {
            $this->upgradeClass->nextParams['status'] = 'error';
            $this->upgradeClass->nextParams['msg'] = sprintf('Unable to generate diff file list between %1$s and %2$s.', _PS_VERSION_, $version);
        } else {
            $this->upgradeClass->getFileConfigurationStorage()->save($diffFileList, UpgradeFiles::diffFileList);
            if (count($diffFileList) > 0) {
                $this->upgradeClass->nextParams['msg'] = $this->upgradeClass->getTranslator()->trans(
                    '%modifiedfiles% files will be modified, %deletedfiles% files will be deleted (if they are found).',
                    array(
                        '%modifiedfiles%' => count($diffFileList['modified']),
                        '%deletedfiles%' => count($diffFileList['deleted']),
                    ),
                    'Modules.Autoupgrade.Admin');
            } else {
                $this->upgradeClass->nextParams['msg'] = $this->upgradeClass->getTranslator()->trans('No diff files found.', array(), 'Modules.Autoupgrade.Admin');
            }
            $this->upgradeClass->nextParams['result'] = $diffFileList;
        }
    }
}