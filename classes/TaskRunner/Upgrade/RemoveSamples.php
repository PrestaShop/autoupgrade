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
use Symfony\Component\Filesystem\Filesystem;

/**
 * Remove all sample files from release archive
 */
class RemoveSamples extends AbstractTask
{
    public function run()
    {
        $this->stepDone = false;
        $this->next = 'removeSamples';

        $removeList = $this->container->getState()->getRemoveList();
        $latestPath = $this->container->getProperty(UpgradeContainer::LATEST_PATH);
        // remove all sample pics in img subdir
        // This part runs at the first call of this step
        if (null === $removeList) {
            $removeList = $this->container->getFilesystemAdapter()->listSampleFiles(array(
                $latestPath.'/prestashop/img/c', '.jpg',
                $latestPath.'/prestashop/img/cms', '.jpg',
                $latestPath.'/prestashop/img/l', '.jpg',
                $latestPath.'/prestashop/img/m', '.jpg',
                $latestPath.'/prestashop/img/os', '.jpg',
                $latestPath.'/prestashop/img/p', '.jpg',
                $latestPath.'/prestashop/img/s', '.jpg',
                $latestPath.'/prestashop/img/scenes', '.jpg',
                $latestPath.'/prestashop/img/st', '.jpg',
                $latestPath.'/prestashop/img/su', '.jpg',
                $latestPath.'/prestashop/img', '404.gif',
                $latestPath.'/prestashop/img', 'favicon.ico',
                $latestPath.'/prestashop/img', 'logo.jpg',
                $latestPath.'/prestashop/img', 'logo_stores.gif',
                $latestPath.'/prestashop/modules/editorial', 'homepage_logo.jpg',
                // remove all override present in the archive
                $latestPath.'/prestashop/override', '.php',
            ));

            $this->container->getState()->setRemoveList($removeList);

            if (count($removeList)) {
                $this->logger->debug($this->translator->trans('Starting to remove %s sample files', array(count($removeList)), 'Modules.Autoupgrade.Admin'));
            }
        }

        $filesystem = new Filesystem;
        for ($i = 0; $i < \AdminSelfUpgrade::$loopRemoveSamples && 0 < count($removeList); $i++) {
            $file = array_shift($removeList);
            try {
                $filesystem->remove($file);
            } catch (\Exception $e) {
                $this->next = 'error';
                $this->logger->error($this->translator->trans(
                    'Error while removing item %itemname%, %itemscount% items left.',
                    array(
                        '%itemname%' => $file,
                        '%itemscount%' => count($removeList)
                    ),
                    'Modules.Autoupgrade.Admin'
                ));
                return false;
            }

            if (count($removeList)) {
                $this->logger->debug($this->translator->trans(
                    '%itemname% items removed. %itemscount% items left.',
                    array(
                        '%itemname%' => $file,
                        '%itemscount%' => count($removeList)
                    ),
                    'Modules.Autoupgrade.Admin'
                ));
            }
        }
        $this->container->getState()->setRemoveList($removeList);

        if (0 >= count($removeList)) {
            $this->stepDone = true;
            $this->next = 'backupFiles';
            $this->logger->info($this->translator->trans('All sample files removed. Now backing up files.', array(), 'Modules.Autoupgrade.Admin'));

            if ($this->container->getUpgradeConfiguration()->get('skip_backup')) {
                $this->next = 'upgradeFiles';
                $this->logger->info($this->translator->trans('All sample files removed. Backup process skipped. Now upgrading files.', array(), 'Modules.Autoupgrade.Admin'));
            }
        }
        return true;
    }
}