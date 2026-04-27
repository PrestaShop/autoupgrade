<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters;

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

    public function loadUpdateConfiguration(): UpdateConfiguration
    {
        return new UpdateConfiguration($this->storage->load(UpgradeFileNames::UPDATE_CONFIG_FILENAME));
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
     * @param UpdateConfiguration|BackupConfiguration|RestoreConfiguration|LanguageConfiguration $config
     *
     * @return bool
     */
    public function save(AbstractConfiguration $config): bool
    {
        switch (get_class($config)) {
            case UpdateConfiguration::class:
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
