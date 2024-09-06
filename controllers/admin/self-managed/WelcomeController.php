<?php

namespace PrestaShop\Module\AutoUpgrade\Controller;

use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\BackupFinder;

class WelcomeController /* Future abstract class */
{
    // TODO: Move this in a parent class

    /** @var UpgradeContainer */
    protected $upgradeContainer;

    public function __construct(UpgradeContainer $upgradeContainer)
    {
        // TODO: add twig into class variable
        $this->upgradeContainer = $upgradeContainer;
    }
    // END OF TODO

    public function index(): string
    {
        $psVersion = $this->upgradeContainer->getProperty($this->upgradeContainer::PS_VERSION);
        $psClass = '';
        $backupPath = $this->upgradeContainer->getProperty($this->upgradeContainer::BACKUP_PATH);
        $backupFinder = new BackupFinder($backupPath);

        if (version_compare($psVersion, '1.7.8.0', '<')) {
            $psClass = 'v1-7-3-0';
        } else if (version_compare($psVersion, '9.0.0', '<')){
            $psClass = 'v1-7-8-0';
        }

        return $this->upgradeContainer->getTwig()->render(
            '@ModuleAutoUpgrade/layouts/layout.html.twig',
            [
                'page' => 'welcome',
                'ps_version' => $psClass,
                'empty_backup' => empty($backupFinder->getAvailableBackups()),
            ]
        );
    }
}
