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

namespace PrestaShop\Module\AutoUpgrade\Parameters;

use Doctrine\Common\Collections\ArrayCollection;

class ConfigurationStorage
{
    /**
     * @var FileStorage
     */
    private $storage;

    public function __construct(FileStorage $storage)
    {
        $this->storage = $storage;
    }

    public function loadUpdateConfiguration(): UpgradeConfiguration
    {
        return new UpgradeConfiguration($this->storage->load(UpgradeFileNames::UPDATE_CONFIG_FILENAME));
    }

    public function loadBackupConfiguration(): BackupConfiguration
    {
        return new BackupConfiguration($this->storage->load(UpgradeFileNames::BACKUP_CONFIG_FILENAME));
    }

    public function loadRestoreConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration($this->storage->load(UpgradeFileNames::RESTORE_CONFIG_FILENAME));
    }

    public function loadLanguageConfiguration(): LanguageConfiguration
    {
        return new LanguageConfiguration($this->storage->load(UpgradeFileNames::LANGUAGE_CONFIG_FILENAME));
    }

    /**
     * @param UpgradeConfiguration|RestoreConfiguration|LanguageConfiguration $config
     *
     * @return bool
     */
    public function save(ArrayCollection $config): bool
    {
        switch (get_class($config)) {
            case UpgradeConfiguration::class:
                $fileName = UpgradeFileNames::UPDATE_CONFIG_FILENAME;
                break;
            case BackupConfiguration::class:
                $fileName = UpgradeFileNames::BACKUP_CONFIG_FILENAME;
                break;
            case RestoreConfiguration::class:
                $fileName = UpgradeFileNames::RESTORE_CONFIG_FILENAME;
                break;
            case LanguageConfiguration::class:
                $fileName = UpgradeFileNames::LANGUAGE_CONFIG_FILENAME;
                break;
            default:
                throw new \InvalidArgumentException('Configuration class ' . $config . ' is unknown.');
        }

        return $this->storage->save($config->toArray(), $fileName);
    }
}
