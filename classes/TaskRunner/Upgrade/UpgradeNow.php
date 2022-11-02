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

/**
 * very first step of the upgrade process. The only thing done is the selection
 * of the next step.
 */
class UpgradeNow extends AbstractTask
{
    public function run()
    {
        $this->logger->info($this->translator->trans('Starting upgrade...', [], 'Modules.Autoupgrade.Admin'));

        $this->container->getWorkspace()->createFolders();

        $channel = $this->container->getUpgradeConfiguration()->get('channel');
        $upgrader = $this->container->getUpgrader();
        $this->next = 'download';
        preg_match('#([0-9]+\.[0-9]+)(?:\.[0-9]+){1,2}#', _PS_VERSION_, $matches);
        $upgrader->branch = $matches[1];
        $upgrader->channel = $channel;
        if ($this->container->getUpgradeConfiguration()->get('channel') == 'private' && !$this->container->getUpgradeConfiguration()->get('private_allow_major')) {
            $upgrader->checkPSVersion(false, ['private', 'minor']);
        } else {
            $upgrader->checkPSVersion(false, ['minor']);
        }

        if ($upgrader->isLastVersion()) {
            $this->next = '';
            $this->logger->info($this->translator->trans('You already have the %s version.', [$upgrader->version_name], 'Modules.Autoupgrade.Admin'));

            return;
        }

        switch ($channel) {
            case 'directory':
                // if channel directory is chosen, we assume it's "ready for use" (samples already removed for example)
                $this->next = 'removeSamples';
                $this->logger->debug($this->translator->trans('Downloading and unzipping steps have been skipped, upgrade process will now remove sample data.', [], 'Modules.Autoupgrade.Admin'));
                $this->logger->info($this->translator->trans('Shop deactivated. Removing sample files...', [], 'Modules.Autoupgrade.Admin'));
                break;
            case 'archive':
                $this->next = 'unzip';
                $this->logger->debug($this->translator->trans('Downloading step has been skipped, upgrade process will now unzip the local archive.', [], 'Modules.Autoupgrade.Admin'));
                $this->logger->info($this->translator->trans('Shop deactivated. Extracting files...', [], 'Modules.Autoupgrade.Admin'));
                break;
            default:
                $this->next = 'download';
                $this->logger->info($this->translator->trans('Shop deactivated. Now downloading... (this can take a while)', [], 'Modules.Autoupgrade.Admin'));
                if ($upgrader->channel == 'private') {
                    $upgrader->link = $this->container->getUpgradeConfiguration()->get('private_release_link');
                    $upgrader->md5 = $this->container->getUpgradeConfiguration()->get('private_release_md5');
                }
                $this->logger->debug($this->translator->trans('Downloaded archive will come from %s', [$upgrader->link], 'Modules.Autoupgrade.Admin'));
                $this->logger->debug($this->translator->trans('MD5 hash will be checked against %s', [$upgrader->md5], 'Modules.Autoupgrade.Admin'));
        }
    }
}
