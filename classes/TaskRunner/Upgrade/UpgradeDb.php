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
use PrestaShop\Module\AutoUpgrade\UpgradeException;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader16;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader17;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader80;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\SettingsFileWriter;

class UpgradeDb extends AbstractTask
{
    public function run()
    {
        try {
            $this->getCoreUpgrader()->doUpgrade();
        } catch (UpgradeException $e) {
            $this->next = 'error';
            $this->error = true;
            foreach ($e->getQuickInfos() as $log) {
                $this->logger->debug($log);
            }
            $this->logger->error($this->translator->trans('Error during database upgrade. You may need to restore your database.', [], 'Modules.Autoupgrade.Admin'));
            $this->logger->error($e->getMessage());

            return false;
        }
        $this->next = 'upgradeModules';
        $this->stepDone = true;
        $this->logger->info($this->translator->trans('Database upgraded. Now upgrading your Addons modules...', [], 'Modules.Autoupgrade.Admin'));

        return true;
    }

    public function getCoreUpgrader()
    {
        if (version_compare($this->container->getState()->getInstallVersion(), '1.7', '<')) {
            return new CoreUpgrader16($this->container, $this->logger);
        }

        if (version_compare($this->container->getState()->getInstallVersion(), '8', '<')) {
            return new CoreUpgrader17($this->container, $this->logger);
        }

        return new CoreUpgrader80($this->container, $this->logger);
    }

    public function init()
    {
        $this->container->getCacheCleaner()->cleanFolders();

        // Migrating settings file
        $this->container->initPrestaShopAutoloader();
        (new SettingsFileWriter($this->translator))->migrateSettingsFile($this->logger);
        parent::init();
    }
}
