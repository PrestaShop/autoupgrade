<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters;

use Configuration;
use Shop;

/**
 * Contains the update process configuration.
 */
class UpdateConfiguration extends AbstractConfiguration
{
    /** @deprecated this configuration is no longer used by update process */
    const PS_AUTOUP_CHANGE_DEFAULT_THEME = 'PS_AUTOUP_CHANGE_DEFAULT_THEME';

    /** PrestaShop configuration variable to disable or not overrides */
    const PS_DISABLE_OVERRIDES = 'PS_DISABLE_OVERRIDES';

    const UNINSTALL_INCOMPATIBLE_MODULES = 'uninstall_incompatible_modules';
    const DISABLE_NON_NATIVE_MODULES = 'disable_non_native_modules';
    const REGENERATE_EMAIL_TEMPLATES = 'regenerate_email_templates';
    const DISABLE_OVERRIDES = 'disable_overrides';
    const CHANNEL = 'channel';
    const UPDATE_TYPE = 'update_type';
    const ARCHIVE_ZIP = 'archive_zip';
    const ARCHIVE_XML = 'archive_xml';
    const ARCHIVE_VERSION_NUM = 'archive_version_num';
    const BACKUP_COMPLETED = 'backup_completed';
    const INSTALLED_LANGUAGES = 'installed_languages';
    const MAX_FILES_PER_BATCH = 'max_files_per_batch';

    const CHANNEL_ONLINE = 'online';
    const CHANNEL_ONLINE_RECOMMENDED = 'online_recommended';
    const CHANNEL_LOCAL = 'local';

    const UPGRADE_CONST_KEYS = [
        self::DISABLE_NON_NATIVE_MODULES,
        self::UNINSTALL_INCOMPATIBLE_MODULES,
        self::PS_AUTOUP_CHANGE_DEFAULT_THEME,
        self::REGENERATE_EMAIL_TEMPLATES,
        self::DISABLE_OVERRIDES,
        self::CHANNEL,
        self::UPDATE_TYPE,
        self::ARCHIVE_ZIP,
        self::ARCHIVE_XML,
        self::ARCHIVE_VERSION_NUM,
        self::MAX_FILES_PER_BATCH,
    ];

    const DEFAULT_VALUES = [
        self::DISABLE_NON_NATIVE_MODULES => true,
        self::UNINSTALL_INCOMPATIBLE_MODULES => true,
        self::PS_AUTOUP_CHANGE_DEFAULT_THEME => false,
        self::REGENERATE_EMAIL_TEMPLATES => true,
        self::BACKUP_COMPLETED => null,
        self::MAX_FILES_PER_BATCH => 400,
    ];

    const CONFIGURATION_KEYS_ABOUT_SHOP = [
        self::INSTALLED_LANGUAGES,
    ];

    const DEFAULT_CHANNEL = self::CHANNEL_ONLINE_RECOMMENDED;
    const ONLINE_CHANNEL_ZIP = 'prestashop.zip';

    /**
     * Get the name of the new release archive.
     */
    public function getLocalChannelZip(): ?string
    {
        return $this->get(self::ARCHIVE_ZIP);
    }

    public function getChannelZip(): ?string
    {
        if ($this->getChannel() === self::CHANNEL_LOCAL) {
            return $this->getLocalChannelZip();
        }

        return self::ONLINE_CHANNEL_ZIP;
    }

    public function getLocalChannelXml(): ?string
    {
        return $this->get(self::ARCHIVE_XML);
    }

    /**
     * Get the version included in the new release.
     */
    public function getLocalChannelVersion(): ?string
    {
        return $this->get(self::ARCHIVE_VERSION_NUM);
    }

    /**
     * Get channel selected on config panel (Minor, major ...).
     *
     * @return UpdateConfiguration::CHANNEL_*|null
     */
    public function getChannel(): ?string
    {
        return $this->get(self::CHANNEL);
    }

    /**
     * @return UpdateConfiguration::CHANNEL_*
     */
    public function getChannelOrDefault(): string
    {
        return $this->getChannel() ?? self::DEFAULT_CHANNEL;
    }

    public function isChannelLocal(): bool
    {
        return $this->getChannelOrDefault() === UpdateConfiguration::CHANNEL_LOCAL;
    }

    public function isChannelOnline(): bool
    {
        return $this->getChannelOrDefault() === UpdateConfiguration::CHANNEL_ONLINE;
    }

    public function isChannelOnlineRecommended(): bool
    {
        return $this->getChannelOrDefault() === UpdateConfiguration::CHANNEL_ONLINE_RECOMMENDED;
    }

    /**
     * @return 'major'|'minor'|'patch'
     */
    public function getUpdateType(): string
    {
        return $this->get(self::UPDATE_TYPE);
    }

    public function isBackupCompleted(): ?bool
    {
        return $this->get(self::BACKUP_COMPLETED);
    }

    /**
     * @return string[]
     */
    public function getInstalledLanguagesIsoCode(): array
    {
        return $this->get(self::INSTALLED_LANGUAGES);
    }

    /**
     * @return int Number of files to handle in a single call to avoid timeouts
     */
    public function getMaxFilesPerBatch(): int
    {
        return $this->computeIntConfiguration(self::MAX_FILES_PER_BATCH);
    }

    /**
     * @return bool True if non-native modules must be disabled during upgrade
     */
    public function shouldDeactivateCustomModules(): bool
    {
        return $this->computeBooleanConfiguration(self::DISABLE_NON_NATIVE_MODULES);
    }

    /**
     * @return bool True if non compatible modules should be uninstall during upgrade
     */
    public function shouldUninstallNonCompatibleModules(): bool
    {
        return $this->computeBooleanConfiguration(self::UNINSTALL_INCOMPATIBLE_MODULES);
    }

    /**
     * @return bool true if we should regenerate the merchant emails
     */
    public function shouldRegenerateMailTemplates(): bool
    {
        return $this->computeBooleanConfiguration(self::REGENERATE_EMAIL_TEMPLATES);
    }

    /**
     * @deprecated
     *
     * @return bool True if we have to set the native theme by default
     */
    public function shouldSwitchToDefaultTheme(): bool
    {
        return $this->computeBooleanConfiguration(self::PS_AUTOUP_CHANGE_DEFAULT_THEME);
    }

    public static function isOverrideAllowed(): bool
    {
        return !Configuration::get(self::PS_DISABLE_OVERRIDES);
    }

    public static function updateDisabledOverride(bool $value, ?int $shopId = null): void
    {
        if ($shopId) {
            Configuration::updateValue(self::PS_DISABLE_OVERRIDES, $value, false, null, (int) $shopId);
        } else {
            Configuration::updateGlobalValue(self::PS_DISABLE_OVERRIDES, $value);
        }
    }

    public static function updatePSDisableOverrides(bool $value): void
    {
        foreach (Shop::getCompleteListOfShopsID() as $id_shop) {
            self::updateDisabledOverride($value, $id_shop);
        }
        self::updateDisabledOverride($value);
    }

    public function hasAllTheShopConfiguration(): bool
    {
        foreach (self::CONFIGURATION_KEYS_ABOUT_SHOP as $key) {
            if ($this->get($key) === null) {
                return false;
            }
        }

        return true;
    }
}
