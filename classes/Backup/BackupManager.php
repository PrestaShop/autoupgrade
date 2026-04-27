<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
