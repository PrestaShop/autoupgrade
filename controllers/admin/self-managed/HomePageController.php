<?php

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\BackupFinder;

class HomePageController extends AbstractPageController
{
    public function index(): string
    {
        $backupPath = $this->upgradeContainer->getProperty($this->upgradeContainer::BACKUP_PATH);
        $backupFinder = new BackupFinder($backupPath);

        return $this->renderPage(
            'home',
            [
                'empty_backup' => empty($backupFinder->getAvailableBackups()),
            ]
        );
    }
}
