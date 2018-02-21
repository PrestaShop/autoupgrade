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
use PrestaShop\Module\AutoUpgrade\Upgrader;

/**
* very first step of the upgrade process. The only thing done is the selection
* of the next step
*/
class UpgradeNow extends AbstractTask
{
    public function run()
    {
        $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('Starting upgrade...', array(), 'Modules.Autoupgrade.Admin');

        $channel = $this->upgradeClass->getUpgradeConfiguration()->get('channel');
        $this->upgradeClass->next = 'download';
        if (!is_object($this->upgradeClass->upgrader)) {
            $this->upgradeClass->upgrader = new Upgrader();
        }
        preg_match('#([0-9]+\.[0-9]+)(?:\.[0-9]+){1,2}#', _PS_VERSION_, $matches);
        $this->upgradeClass->upgrader->branch = $matches[1];
        $this->upgradeClass->upgrader->channel = $channel;
        if ($this->upgradeClass->getUpgradeConfiguration()->get('channel') == 'private' && !$this->upgradeClass->getUpgradeConfiguration()->get('private_allow_major')) {
            $this->upgradeClass->upgrader->checkPSVersion(false, array('private', 'minor'));
        } else {
            $this->upgradeClass->upgrader->checkPSVersion(false, array('minor'));
        }

        switch ($channel) {
            case 'directory':
                // if channel directory is chosen, we assume it's "ready for use" (samples already removed for example)
                $this->upgradeClass->next = 'removeSamples';
                $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('Skip downloading and unzipping steps, upgrade process will now remove sample data.', array(), 'Modules.Autoupgrade.Admin');
                $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('Shop deactivated. Removing sample files...', array(), 'Modules.Autoupgrade.Admin');
                break;
            case 'archive':
                $this->upgradeClass->next = 'unzip';
                $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('Skip downloading step, upgrade process will now unzip the local archive.', array(), 'Modules.Autoupgrade.Admin');
                $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('Shop deactivated. Extracting files...', array(), 'Modules.Autoupgrade.Admin');
                break;
            default:
                $this->upgradeClass->next = 'download';
                $this->upgradeClass->next_desc = $this->upgradeClass->getTranslator()->trans('Shop deactivated. Now downloading... (this can take a while)', array(), 'Modules.Autoupgrade.Admin');
                if ($this->upgradeClass->upgrader->channel == 'private') {
                    $this->upgradeClass->upgrader->link = $this->upgradeClass->getUpgradeConfiguration()->get('private_release_link');
                    $this->upgradeClass->upgrader->md5 = $this->upgradeClass->getUpgradeConfiguration()->get('private_release_md5');
                }
                $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('Downloaded archive will come from %s', array($this->upgradeClass->upgrader->link), 'Modules.Autoupgrade.Admin');
                $this->upgradeClass->nextQuickInfo[] = $this->upgradeClass->getTranslator()->trans('MD5 hash will be checked against %s', array($this->upgradeClass->upgrader->md5), 'Modules.Autoupgrade.Admin');
        }
    }
}