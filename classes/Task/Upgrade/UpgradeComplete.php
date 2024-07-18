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

use Exception;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\Task\AbstractTask;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;

/**
 * Ends the upgrade process and displays the success message.
 */
class UpgradeComplete extends AbstractTask
{
    const TASK_TYPE = 'upgrade';

    /**
     * @throws Exception
     */
    public function run(): int
    {
        $this->container->getState()->setProgressPercentage(
            $this->container->getCompletionCalculator()->getBasePercentageOfTask(self::class)
        );

        $this->logger->info($this->container->getState()->getWarningExists() ?
            $this->translator->trans('Upgrade process done, but some warnings have been found.') :
            $this->translator->trans('Upgrade process done. Congratulations! You can now reactivate your shop.')
        );

        $this->next = '';

        if ($this->container->getUpgradeConfiguration()->get('channel') != 'archive' && file_exists($this->container->getFilePath()) && unlink($this->container->getFilePath())) {
            $this->logger->debug($this->translator->trans('%s removed', [$this->container->getFilePath()]));
        } elseif (is_file($this->container->getFilePath())) {
            $this->logger->debug('<strong>' . $this->translator->trans('Please remove %s by FTP', [$this->container->getFilePath()]) . '</strong>');
        }

        if ($this->container->getUpgradeConfiguration()->get('channel') != 'directory' && file_exists($this->container->getProperty(UpgradeContainer::LATEST_PATH)) && FilesystemAdapter::deleteDirectory($this->container->getProperty(UpgradeContainer::LATEST_PATH))) {
            $this->logger->debug($this->translator->trans('%s removed', [$this->container->getProperty(UpgradeContainer::LATEST_PATH)]));
        } elseif (is_dir($this->container->getProperty(UpgradeContainer::LATEST_PATH))) {
            $this->logger->debug('<strong>' . $this->translator->trans('Please remove %s by FTP', [$this->container->getProperty(UpgradeContainer::LATEST_PATH)]) . '</strong>');
        }

        // removing temporary files
        $this->container->getFileConfigurationStorage()->cleanAll();
        $this->container->getAnalytics()->track('Upgrade Succeeded', Analytics::WITH_UPGRADE_PROPERTIES);

        return ExitCode::SUCCESS;
    }
}
