<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
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
            if (!$this->container->getFilesystemAdapter()->isReleaseValid($latestPath)) {
                $this->logger->error($this->translator->trans('Could not assert the folder %s contains a valid PrestaShop release, exiting.', [$latestPath], 'Modules.Autoupgrade.Admin'));
                $this->logger->error($this->translator->trans('A file may be missing, or the release is stored in a subfolder by mistake.', [], 'Modules.Autoupgrade.Admin'));
                $this->next = 'error';

                return;
            }

            $removeList = $this->container->getFilesystemAdapter()->listSampleFilesFromArray([
                ['path' => $latestPath . '/img/c', 'filter' => '.jpg'],
                ['path' => $latestPath . '/img/cms', 'filter' => '.jpg'],
                ['path' => $latestPath . '/img/l', 'filter' => '.jpg'],
                ['path' => $latestPath . '/img/m', 'filter' => '.jpg'],
                ['path' => $latestPath . '/img/os', 'filter' => '.jpg'],
                ['path' => $latestPath . '/img/p', 'filter' => '.jpg'],
                ['path' => $latestPath . '/img/s', 'filter' => '.jpg'],
                ['path' => $latestPath . '/img/scenes', 'filter' => '.jpg'],
                ['path' => $latestPath . '/img/st', 'filter' => '.jpg'],
                ['path' => $latestPath . '/img/su', 'filter' => '.jpg'],
                ['path' => $latestPath . '/img', 'filter' => '404.gif'],
                ['path' => $latestPath . '/img', 'filter' => 'favicon.ico'],
                ['path' => $latestPath . '/img', 'filter' => 'logo.jpg'],
                ['path' => $latestPath . '/img', 'filter' => 'logo_stores.gif'],
                ['path' => $latestPath . '/modules/editorial', 'filter' => 'homepage_logo.jpg'],
                // remove all override present in the archive
                ['path' => $latestPath . '/override', 'filter' => '.php'],
            ]);

            $this->container->getState()->setRemoveList(
                array_reverse($removeList)
            );

            if (count($removeList)) {
                $this->logger->debug(
                    $this->translator->trans('Starting to remove %s sample files',
                        [count($removeList)], 'Modules.Autoupgrade.Admin'));
            }
        }

        $filesystem = new Filesystem();
        for ($i = 0; $i < $this->container->getUpgradeConfiguration()->getNumberOfFilesPerCall() && 0 < count($removeList); ++$i) {
            $file = array_pop($removeList);
            try {
                $filesystem->remove($file);
            } catch (\Exception $e) {
                $this->next = 'error';
                $this->logger->error($this->translator->trans(
                    'Error while removing item %itemname%, %itemscount% items left.',
                    [
                        '%itemname%' => $file,
                        '%itemscount%' => count($removeList),
                    ],
                    'Modules.Autoupgrade.Admin'
                ));

                return false;
            }

            if (count($removeList)) {
                $this->logger->debug($this->translator->trans(
                    '%itemname% item removed. %itemscount% items left.',
                    [
                        '%itemname%' => $file,
                        '%itemscount%' => count($removeList),
                    ],
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
                    [],
                    'Modules.Autoupgrade.Admin'
            ));

            if ($this->container->getUpgradeConfiguration()->get('skip_backup')) {
                $this->next = 'upgradeFiles';
                $this->logger->info(
                    $this->translator->trans(
                        'All sample files removed. Backup process skipped. Now upgrading files.',
                        [],
                        'Modules.Autoupgrade.Admin'
                ));
            }
        }

        return true;
    }
}
