<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\AutoUpgrade\Backup;

use InvalidArgumentException;
use PrestaShop\Module\AutoUpgrade\Analytics;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Component\Filesystem\Filesystem;

class BackupManager
{
    /** @var Translator */
    private $translator;
    /** @var BackupFinder */
    private $backupFinder;
    /** @var Analytics */
    private $analytics;

    public function __construct(Translator $translator, BackupFinder $backupFinder, Analytics $analytics)
    {
        $this->translator = $translator;
        $this->backupFinder = $backupFinder;
        $this->analytics = $analytics;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteBackup(string $backupName): void
    {
        if (!in_array($backupName, $this->backupFinder->getAvailableBackups())) {
            throw new InvalidArgumentException($this->translator->trans('Backup requested for deletion does not exist.'));
        }

        $filesystem = new Filesystem();
        $filesystem->remove([
            $this->backupFinder->getBackupPath() . DIRECTORY_SEPARATOR . BackupFinder::BACKUP_ZIP_NAME_PREFIX . $backupName . '.zip',
            $this->backupFinder->getBackupPath() . DIRECTORY_SEPARATOR . $backupName,
        ]);

        $this->backupFinder->resetBackupList();
        $this->analytics->track('Backup Deleted');
    }
}
