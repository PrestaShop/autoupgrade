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
use Symfony\Component\Filesystem\Filesystem;

/**
 * Remove all sample files from release archive
 */
class RemoveSamples extends AbstractTask
{
    public function run()
    {
        $this->upgradeClass->stepDone = false;
        $this->upgradeClass->next = 'removeSamples';

        // remove all sample pics in img subdir
        // This part runs at the first call of this step
        if (!isset($this->upgradeClass->currentParams['removeList'])) {
            $this->upgradeClass->nextParams['removeList'] = $this->upgradeClass->getFilesystemAdapter()->listSampleFiles(array(
                $this->upgradeClass->latestPath.'/prestashop/img/c', '.jpg',
                $this->upgradeClass->latestPath.'/prestashop/img/cms', '.jpg',
                $this->upgradeClass->latestPath.'/prestashop/img/l', '.jpg',
                $this->upgradeClass->latestPath.'/prestashop/img/m', '.jpg',
                $this->upgradeClass->latestPath.'/prestashop/img/os', '.jpg',
                $this->upgradeClass->latestPath.'/prestashop/img/p', '.jpg',
                $this->upgradeClass->latestPath.'/prestashop/img/s', '.jpg',
                $this->upgradeClass->latestPath.'/prestashop/img/scenes', '.jpg',
                $this->upgradeClass->latestPath.'/prestashop/img/st', '.jpg',
                $this->upgradeClass->latestPath.'/prestashop/img/su', '.jpg',
                $this->upgradeClass->latestPath.'/prestashop/img', '404.gif',
                $this->upgradeClass->latestPath.'/prestashop/img', 'favicon.ico',
                $this->upgradeClass->latestPath.'/prestashop/img', 'logo.jpg',
                $this->upgradeClass->latestPath.'/prestashop/img', 'logo_stores.gif',
                $this->upgradeClass->latestPath.'/prestashop/modules/editorial', 'homepage_logo.jpg',
                // remove all override present in the archive
                $this->upgradeClass->latestPath.'/prestashop/override', '.php',
            ));

            if (count($this->upgradeClass->nextParams['removeList']) > 0) {
                $this->logger->debug($this->upgradeClass->getTranslator()->trans('Starting to remove %s sample files', array(count($this->upgradeClass->nextParams['removeList'])), 'Modules.Autoupgrade.Admin'));
            }
        }

        $filesystem = new Filesystem;
        for ($i = 0; $i < \AdminSelfUpgrade::$loopRemoveSamples && 0 < count($this->upgradeClass->nextParams['removeList']); $i++) {
            $file = array_shift($this->upgradeClass->nextParams['removeList']);
            try {
                $filesystem->remove($file);
            } catch (\Exception $e) {
                $this->upgradeClass->next = 'error';
                $this->logger->error($this->upgradeClass->getTranslator()->trans(
                    'Error while removing item %itemname%, %itemscount% items left.',
                    array(
                        '%itemname%' => $file,
                        '%itemscount%' => count($this->upgradeClass->nextParams['removeList'])
                    ),
                    'Modules.Autoupgrade.Admin'
                ));
                return false;
            }

            if (count($this->upgradeClass->nextParams['removeList'])) {
                $this->logger->debug($this->upgradeClass->getTranslator()->trans(
                    '%itemname% items removed. %itemscount% items left.',
                    array(
                        '%itemname%' => $file,
                        '%itemscount%' => count($this->upgradeClass->nextParams['removeList'])
                    ),
                    'Modules.Autoupgrade.Admin'
                ));
            }
        }

        if (0 >= count($this->upgradeClass->nextParams['removeList'])) {
            $this->upgradeClass->stepDone = true;
            $this->upgradeClass->next = 'backupFiles';
            $this->logger->info($this->upgradeClass->getTranslator()->trans('All sample files removed. Now backing up files.', array(), 'Modules.Autoupgrade.Admin'));

            if ($this->upgradeClass->getUpgradeConfiguration()->get('skip_backup')) {
                $this->upgradeClass->next = 'upgradeFiles';
                $this->logger->info($this->upgradeClass->getTranslator()->trans('All sample files removed. Backup process skipped. Now upgrading files.', array(), 'Modules.Autoupgrade.Admin'));
            }
        }
        return true;
    }
}