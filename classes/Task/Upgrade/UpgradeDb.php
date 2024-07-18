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

namespace PrestaShop\Module\AutoUpgrade\Task\Upgrade;

use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader17;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader80;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\CoreUpgrader\CoreUpgrader81;

class UpgradeDb extends AbstractTask
{
    const TASK_TYPE = 'upgrade';

    public function run(): int
    {
        $this->container->getState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        try {
            $this->getCoreUpgrader()->doUpgrade();
        } catch (UpgradeException $e) {
            $this->next = 'error';
            $this->setErrorFlag();
            foreach ($e->getQuickInfos() as $log) {
                $this->logger->debug($log);
            }
            $this->logger->error($this->translator->trans('Error during database upgrade. You may need to restore your database.'));
            $this->logger->error($e->getMessage());

            return ExitCode::FAIL;
        }
        $this->next = 'upgradeModules';
        $this->stepDone = true;
        $this->logger->info($this->translator->trans('Database upgraded. Now upgrading your Addons modules...'));

        return ExitCode::SUCCESS;
    }

    public function getCoreUpgrader(): CoreUpgrader
    {
        if (version_compare($this->container->getState()->getInstallVersion(), '8', '<')) {
            return new CoreUpgrader17($this->container, $this->logger);
        }

        if (version_compare($this->container->getState()->getInstallVersion(), '8.1', '<')) {
            return new CoreUpgrader80($this->container, $this->logger);
        }

        return new CoreUpgrader81($this->container, $this->logger);
    }

    public function init(): void
    {
        $this->logger->info($this->translator->trans('Cleaning file cache'));
        $this->container->getCacheCleaner()->cleanFolders();
        $this->logger->info($this->translator->trans('Running opcache_reset'));
        $this->container->resetOpcache();

        // Migrating settings file
        $this->container->initPrestaShopAutoloader();
        parent::init();
    }
}
