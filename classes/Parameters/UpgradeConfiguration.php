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

use Configuration;
use Doctrine\Common\Collections\ArrayCollection;
use Shop;
use UnexpectedValueException;

/**
 * Contains the module configuration (form params).
 *
 * @extends ArrayCollection<string, mixed>
 */
class UpgradeConfiguration extends ArrayCollection
{
    const PS_AUTOUP_CUSTOM_MOD_DESACT = 'PS_AUTOUP_CUSTOM_MOD_DESACT';
    const PS_AUTOUP_CHANGE_DEFAULT_THEME = 'PS_AUTOUP_CHANGE_DEFAULT_THEME';
    const PS_AUTOUP_REGEN_EMAIL = 'PS_AUTOUP_REGEN_EMAIL';
    const PS_AUTOUP_KEEP_IMAGES = 'PS_AUTOUP_KEEP_IMAGES';
    const PS_DISABLE_OVERRIDES = 'PS_DISABLE_OVERRIDES';
    const CHANNEL = 'channel';
    const ARCHIVE_ZIP = 'archive_zip';
    const ARCHIVE_XML = 'archive_xml';
    const ARCHIVE_VERSION_NUM = 'archive_version_num';
    const BACKUP_COMPLETED = 'backup_completed';
    const INSTALLED_LANGUAGES = 'installed_languages';

    const CHANNEL_ONLINE = 'online';
    const CHANNEL_LOCAL = 'local';

    const UPGRADE_CONST_KEYS = [
        self::PS_AUTOUP_CUSTOM_MOD_DESACT,
        self::PS_AUTOUP_CHANGE_DEFAULT_THEME,
        self::PS_AUTOUP_REGEN_EMAIL,
        self::PS_AUTOUP_KEEP_IMAGES,
        self::PS_DISABLE_OVERRIDES,
        self::CHANNEL,
        self::ARCHIVE_ZIP,
        self::ARCHIVE_XML,
        self::ARCHIVE_VERSION_NUM,
    ];

    const PS_CONST_DEFAULT_VALUE = [
        self::PS_AUTOUP_CUSTOM_MOD_DESACT => true,
        self::PS_AUTOUP_CHANGE_DEFAULT_THEME => false,
        self::PS_AUTOUP_REGEN_EMAIL => true,
        self::PS_AUTOUP_KEEP_IMAGES => true,
        self::BACKUP_COMPLETED => null,
    ];

    const CONFIGURATION_KEYS_ABOUT_SHOP = [
        self::INSTALLED_LANGUAGES,
    ];

    const DEFAULT_CHANNEL = self::CHANNEL_ONLINE;
    const ONLINE_CHANNEL_ZIP = 'prestashop.zip';

    /**
     * Performance settings, if your server has a low memory size, lower these values.
     *
     * @var array<string, int>
     */
    private const PERFORMANCE_VALUES = [
        'loopFiles' => 400, // files
        'loopTime' => 6, // seconds
        'maxBackupFileSize' => 15728640, // bytes
        'maxWrittenAllowed' => 4194304, // bytes
    ];

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
     * @return UpgradeConfiguration::CHANNEL_*|null
     */
    public function getChannel(): ?string
    {
        return $this->get(self::CHANNEL);
    }

    /**
     * @return UpgradeConfiguration::CHANNEL_*
     */
    public function getChannelOrDefault(): string
    {
        return $this->getChannel() ?? self::DEFAULT_CHANNEL;
    }

    public function isChannelLocal(): bool
    {
        return $this->getChannelOrDefault() === UpgradeConfiguration::CHANNEL_LOCAL;
    }

    public function isChannelOnline(): bool
    {
        return $this->getChannelOrDefault() === UpgradeConfiguration::CHANNEL_ONLINE;
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
    public function getNumberOfFilesPerCall(): int
    {
        return $this::PERFORMANCE_VALUES['loopFiles'];
    }

    /**
     * @return int Number of seconds allowed before having to make another request
     */
    public function getTimePerCall(): int
    {
        return $this::PERFORMANCE_VALUES['loopTime'];
    }

    /**
     * @return int Kind of reference for SQL file creation, giving a file size before another request is needed
     */
    public function getMaxSizeToWritePerCall(): int
    {
        return $this::PERFORMANCE_VALUES['maxWrittenAllowed'];
    }

    /**
     * @return int Max file size allowed in backup
     */
    public function getMaxFileToBackup(): int
    {
        return $this::PERFORMANCE_VALUES['maxBackupFileSize'];
    }

    /**
     * @return bool True if the autoupgrade module backup should include the images
     */
    public function shouldBackupImages(): bool
    {
        return $this->computeBooleanConfiguration(self::PS_AUTOUP_KEEP_IMAGES);
    }

    /**
     * @return bool True if non-native modules must be disabled during upgrade
     */
    public function shouldDeactivateCustomModules(): bool
    {
        return $this->computeBooleanConfiguration(self::PS_AUTOUP_CUSTOM_MOD_DESACT);
    }

    /**
     * @return bool true if we should regenerate the merchant emails
     */
    public function shouldRegenerateMailTemplates(): bool
    {
        return $this->computeBooleanConfiguration(self::PS_AUTOUP_REGEN_EMAIL);
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

    private function computeBooleanConfiguration(string $const): bool
    {
        $currentValue = $this->get($const);
        $defaultValue = self::PS_CONST_DEFAULT_VALUE[$const];

        if ($currentValue === null) {
            return $defaultValue;
        }

        $currentValue = filter_var($currentValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $currentValue !== null ? $currentValue : $defaultValue;
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

    /**
     * @param array<string, mixed> $array
     *
     * @return void
     *
     * @throws UnexpectedValueException
     */
    public function merge(array $array = []): void
    {
        foreach ($array as $key => $value) {
            $this->set($key, $value);
        }
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
