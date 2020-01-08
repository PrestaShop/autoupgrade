<?php

/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\AutoUpgrade\TaskRunner\Upgrade;

use PrestaShop\Module\AutoUpgrade\TaskRunner\AbstractTask;
use PrestaShop\Module\AutoUpgrade\UpgradeException;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader16;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader17;
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
            $this->logger->error($this->translator->trans('Error during database upgrade. You may need to restore your database.', array(), 'Modules.Autoupgrade.Admin'));
            $this->logger->error($e->getMessage());

            return false;
        }
        $this->next = 'upgradeModules';
        $this->logger->info($this->translator->trans('Database upgraded. Now upgrading your Addons modules...', array(), 'Modules.Autoupgrade.Admin'));

        return true;
    }

    public function getCoreUpgrader()
    {
        if (version_compare($this->container->getState()->getInstallVersion(), '1.7.0.0', '<=')) {
            return new CoreUpgrader16($this->container, $this->logger);
        }

        return new CoreUpgrader17($this->container, $this->logger);
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
