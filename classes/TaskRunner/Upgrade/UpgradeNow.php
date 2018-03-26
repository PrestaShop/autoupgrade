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
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

/**
* very first step of the upgrade process. The only thing done is the selection
* of the next step
*/
class UpgradeNow extends AbstractTask
{
    public function run()
    {
        $this->logger->info($this->translator->trans('Starting upgrade...', array(), 'Modules.Autoupgrade.Admin'));

        $this->createFolders();

        $channel = $this->container->getUpgradeConfiguration()->get('channel');
        $upgrader = $this->container->getUpgrader();
        $this->next = 'download';
        preg_match('#([0-9]+\.[0-9]+)(?:\.[0-9]+){1,2}#', _PS_VERSION_, $matches);
        $upgrader->branch = $matches[1];
        $upgrader->channel = $channel;
        if ($this->container->getUpgradeConfiguration()->get('channel') == 'private' && !$this->container->getUpgradeConfiguration()->get('private_allow_major')) {
            $upgrader->checkPSVersion(false, array('private', 'minor'));
        } else {
            $upgrader->checkPSVersion(false, array('minor'));
        }

        switch ($channel) {
            case 'directory':
                // if channel directory is chosen, we assume it's "ready for use" (samples already removed for example)
                $this->next = 'removeSamples';
                $this->logger->debug($this->translator->trans('Skip downloading and unzipping steps, upgrade process will now remove sample data.', array(), 'Modules.Autoupgrade.Admin'));
                $this->logger->info($this->translator->trans('Shop deactivated. Removing sample files...', array(), 'Modules.Autoupgrade.Admin'));
                break;
            case 'archive':
                $this->next = 'unzip';
                $this->logger->debug($this->translator->trans('Skip downloading step, upgrade process will now unzip the local archive.', array(), 'Modules.Autoupgrade.Admin'));
                $this->logger->info($this->translator->trans('Shop deactivated. Extracting files...', array(), 'Modules.Autoupgrade.Admin'));
                break;
            default:
                $this->next = 'download';
                $this->logger->info($this->translator->trans('Shop deactivated. Now downloading... (this can take a while)', array(), 'Modules.Autoupgrade.Admin'));
                if ($upgrader->channel == 'private') {
                    $upgrader->link = $this->container->getUpgradeConfiguration()->get('private_release_link');
                    $upgrader->md5 = $this->container->getUpgradeConfiguration()->get('private_release_md5');
                }
                $this->logger->debug($this->translator->trans('Downloaded archive will come from %s', array($upgrader->link), 'Modules.Autoupgrade.Admin'));
                $this->logger->debug($this->translator->trans('MD5 hash will be checked against %s', array($upgrader->md5), 'Modules.Autoupgrade.Admin'));
        }
    }

    public function createFolders()
    {
        $paths = array(
            UpgradeContainer::WORKSPACE_PATH, UpgradeContainer::BACKUP_PATH,
            UpgradeContainer::DOWNLOAD_PATH, UpgradeContainer::LATEST_PATH,
            UpgradeContainer::TMP_PATH);

        foreach ($paths as $pathName) {
            $path = $this->container->getProperty($pathName);
            if (!file_exists($path) && !mkdir($path)) {
                $this->logger->error($this->trans('Unable to create directory %s', array($path), 'Modules.Autoupgrade.Admin'));
            }
            if (!is_writable($path)) {
                $this->logger->error($this->trans('Unable to write in the directory "%s"', array($path), 'Modules.Autoupgrade.Admin'));
            }
        }
    }
}