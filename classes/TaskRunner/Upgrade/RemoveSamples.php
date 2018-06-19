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
 * Remove all sample files from release archive.
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
            $removeList = $this->container->getFilesystemAdapter()->listSampleFilesFromArray(array(
                array('path' => $latestPath.'/prestashop/img/c', 'filter' => '.jpg'),
                array('path' => $latestPath.'/prestashop/img/cms', 'filter' => '.jpg'),
                array('path' => $latestPath.'/prestashop/img/l', 'filter' => '.jpg'),
                array('path' => $latestPath.'/prestashop/img/m', 'filter' => '.jpg'),
                array('path' => $latestPath.'/prestashop/img/os', 'filter' => '.jpg'),
                array('path' => $latestPath.'/prestashop/img/p', 'filter' => '.jpg'),
                array('path' => $latestPath.'/prestashop/img/s', 'filter' => '.jpg'),
                array('path' => $latestPath.'/prestashop/img/scenes', 'filter' => '.jpg'),
                array('path' => $latestPath.'/prestashop/img/st', 'filter' => '.jpg'),
                array('path' => $latestPath.'/prestashop/img/su', 'filter' => '.jpg'),
                array('path' => $latestPath.'/prestashop/img', 'filter' => '404.gif'),
                array('path' => $latestPath.'/prestashop/img', 'filter' => 'favicon.ico'),
                array('path' => $latestPath.'/prestashop/img', 'filter' => 'logo.jpg'),
                array('path' => $latestPath.'/prestashop/img', 'filter' => 'logo_stores.gif'),
                array('path' => $latestPath.'/prestashop/modules/editorial', 'filter' => 'homepage_logo.jpg'),
                // remove all override present in the archive
                array('path' => $latestPath.'/prestashop/override', 'filter' => '.php'),
            ));

            $this->container->getState()->setRemoveList($removeList);

            if (count($removeList)) {
                $this->logger->debug(
                    $this->translator->trans('Starting to remove %s sample files',
                        array(count($removeList)), 'Modules.Autoupgrade.Admin'));
            }
        }

        $filesystem = new Filesystem();
        for ($i = 0; $i < $this->container->getUpgradeConfiguration()->getNumberOfFilesPerCall() && 0 < count($removeList); ++$i) {
            $file = array_shift($removeList);
            try {
                $filesystem->remove($file);
            } catch (\Exception $e) {
                $this->next = 'error';
                $this->logger->error($this->translator->trans(
                    'Error while removing item %itemname%, %itemscount% items left.',
                    array(
                        '%itemname%' => $file,
                        '%itemscount%' => count($removeList),
                    ),
                    'Modules.Autoupgrade.Admin'
                ));

                return false;
            }

            if (count($removeList)) {
                $this->logger->debug($this->translator->trans(
                    '%itemname% item removed. %itemscount% items left.',
                    array(
                        '%itemname%' => $file,
                        '%itemscount%' => count($removeList),
                    ),
                    'Modules.Autoupgrade.Admin'
                ));
            }
        }
        $this->container->getState()->setRemoveList($removeList);

        if (0 >= count($removeList)) {
            $this->stepDone = true;
            $this->next = 'backupFiles';
            $this->logger->info(
                $this->translator->trans(
                    'All sample files removed. Now backing up files.',
                    array(),
                    'Modules.Autoupgrade.Admin'
            ));

            if ($this->container->getUpgradeConfiguration()->get('skip_backup')) {
                $this->next = 'upgradeFiles';
                $this->logger->info(
                    $this->translator->trans(
                        'All sample files removed. Backup process skipped. Now upgrading files.',
                        array(),
                        'Modules.Autoupgrade.Admin'
                ));
            }
        }

        return true;
    }
}
