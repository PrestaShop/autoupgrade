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

namespace PrestaShop\Module\AutoUpgrade\Parameters;

use Configuration;
use Doctrine\Common\Collections\ArrayCollection;
use PrestaShop\Module\AutoUpgrade\Upgrader;
use Shop;
use UnexpectedValueException;

/**
 * Contains the module configuration (form params).
 *
 * @extends ArrayCollection<string, mixed>
 */
class UpgradeConfiguration extends ArrayCollection
{
    const UPGRADE_CONST_KEYS = [
        'PS_AUTOUP_CUSTOM_MOD_DESACT',
        'PS_AUTOUP_CHANGE_DEFAULT_THEME',
        'PS_AUTOUP_KEEP_MAILS',
        'PS_AUTOUP_BACKUP',
        'PS_AUTOUP_KEEP_IMAGES',
        'PS_DISABLE_OVERRIDES',
        'channel',
        'archive_zip',
        'archive_xml',
        'archive_version_num',
    ];

    const PS_CONST_DEFAULT_VALUE = [
        'PS_AUTOUP_CUSTOM_MOD_DESACT' => true,
        'PS_AUTOUP_CHANGE_DEFAULT_THEME' => false,
        'PS_AUTOUP_KEEP_MAILS' => false,
        'PS_AUTOUP_BACKUP' => true,
        'PS_AUTOUP_KEEP_IMAGES' => true,
    ];

    const DEFAULT_CHANNEL = Upgrader::CHANNEL_ONLINE;
    const DEFAULT_FILENAME = 'prestashop.zip';

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

    /** @var ConfigurationValidator */
    private $validator;

    /**
     * Get the name of the new release archive.
     */
    public function getArchiveZip(): ?string
    {
        return $this->get('archive_zip');
    }

    public function getArchiveXml(): ?string
    {
        return $this->get('archive_xml');
    }

    /**
     * Get the version included in the new release.
     */
    public function getArchiveVersion(): ?string
    {
        return $this->get('archive_version_num');
    }

    /**
     * Get channel selected on config panel (Minor, major ...).
     */
    public function getChannel(): ?string
    {
        return $this->get('channel');
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

    public function shouldBackupFilesAndDatabase(): bool
    {
        $currentValue = filter_var($this->get('PS_AUTOUP_BACKUP'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $currentValue !== null ? $currentValue : self::PS_CONST_DEFAULT_VALUE['PS_AUTOUP_BACKUP'];
    }

    /**
     * @return bool True if the autoupgrade module backup should include the images
     */
    public function shouldBackupImages(): bool
    {
        $currentValue = filter_var($this->get('PS_AUTOUP_KEEP_IMAGES'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $currentValue !== null ? $currentValue : self::PS_CONST_DEFAULT_VALUE['PS_AUTOUP_KEEP_IMAGES'];
    }

    /**
     * @return bool True if non-native modules must be disabled during upgrade
     */
    public function shouldDeactivateCustomModules(): bool
    {
        $currentValue = filter_var($this->get('PS_AUTOUP_CUSTOM_MOD_DESACT'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $currentValue !== null ? $currentValue : self::PS_CONST_DEFAULT_VALUE['PS_AUTOUP_CUSTOM_MOD_DESACT'];
    }

    /**
     * @return bool true if we should keep the merchant emails untouched
     */
    public function shouldKeepMails(): bool
    {
        $currentValue = filter_var($this->get('PS_AUTOUP_KEEP_MAILS'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $currentValue !== null ? $currentValue : self::PS_CONST_DEFAULT_VALUE['PS_AUTOUP_KEEP_MAILS'];
    }

    /**
     * @return bool True if we have to set the native theme by default
     */
    public function shouldSwitchToDefaultTheme(): bool
    {
        $currentValue = filter_var($this->get('PS_AUTOUP_CHANGE_DEFAULT_THEME'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $currentValue !== null ? $currentValue : self::PS_CONST_DEFAULT_VALUE['PS_AUTOUP_CHANGE_DEFAULT_THEME'];
    }

    public static function isOverrideAllowed(): bool
    {
        return (bool) Configuration::get('PS_DISABLE_OVERRIDES');
    }

    public static function updateDisabledOverride(bool $value, ?int $shopId = null): void
    {
        if ($shopId) {
            Configuration::updateValue('PS_DISABLE_OVERRIDES', $value, false, null, (int) $shopId);
        } else {
            Configuration::updateGlobalValue('PS_DISABLE_OVERRIDES', $value);
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

    /**
     * @param array<string, mixed> $array
     *
     * @return void
     *
     * @throws UnexpectedValueException
     */
    public function validate(array $array = []): void
    {
        if ($this->validator === null) {
            $this->validator = new ConfigurationValidator();
        }

        $this->validator->validate($array);
    }
}
