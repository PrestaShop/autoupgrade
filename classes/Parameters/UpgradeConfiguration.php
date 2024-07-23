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
use Shop;

/**
 * Contains the module configuration (form params).
 *
 * @extends ArrayCollection<string, mixed>
 */
class UpgradeConfiguration extends ArrayCollection
{
    const UPGRADE_CONST_KEYS = [
        'PS_AUTOUP_PERFORMANCE',
        'PS_AUTOUP_CUSTOM_MOD_DESACT',
        'PS_AUTOUP_UPDATE_DEFAULT_THEME',
        'PS_AUTOUP_CHANGE_DEFAULT_THEME',
        'PS_AUTOUP_UPDATE_RTL_FILES',
        'PS_AUTOUP_KEEP_MAILS',
        'PS_AUTOUP_BACKUP',
        'PS_AUTOUP_KEEP_IMAGES',
        'PS_DISABLE_OVERRIDES',
    ];

    const PS_CONST_DEFAULT_VALUE = [
        'PS_AUTOUP_PERFORMANCE' => 1,
        'PS_AUTOUP_CUSTOM_MOD_DESACT' => 1,
        'PS_AUTOUP_UPDATE_DEFAULT_THEME' => 1,
        'PS_AUTOUP_CHANGE_DEFAULT_THEME' => 0,
        'PS_AUTOUP_UPDATE_RTL_FILES' => 1,
        'PS_AUTOUP_KEEP_MAILS' => 0,
        'PS_AUTOUP_BACKUP' => 1,
        'PS_AUTOUP_KEEP_IMAGES' => 1,
    ];

    /**
     * Performance settings, if your server has a low memory size, lower these values.
     *
     * @var array<string, int[]>
     */
    private const PERFORMANCE_VALUES = [
        'loopFiles' => [400, 800, 1600], // files
        'loopTime' => [6, 12, 25], // seconds
        'maxBackupFileSize' => [15728640, 31457280, 62914560], // bytes
        'maxWrittenAllowed' => [4194304, 8388608, 16777216], // bytes
    ];

    /**
     * Get the name of the new release archive.
     */
    public function getArchiveFilename(): string
    {
        return $this->get('archive.filename');
    }

    /**
     * Get the version included in the new release.
     */
    public function getArchiveVersion(): string
    {
        return $this->get('archive.version_num');
    }

    /**
     * Get channel selected on config panel (Minor, major ...).
     */
    public function getChannel(): string
    {
        return $this->get('channel');
    }

    /**
     * @return int Number of files to handle in a single call to avoid timeouts
     */
    public function getNumberOfFilesPerCall(): int
    {
        return $this::PERFORMANCE_VALUES['loopFiles'][$this->getPerformanceLevel()];
    }

    /**
     * @return int Number of seconds allowed before having to make another request
     */
    public function getTimePerCall(): int
    {
        return $this::PERFORMANCE_VALUES['loopTime'][$this->getPerformanceLevel()];
    }

    /**
     * @return int Kind of reference for SQL file creation, giving a file size before another request is needed
     */
    public function getMaxSizeToWritePerCall(): int
    {
        return $this::PERFORMANCE_VALUES['maxWrittenAllowed'][$this->getPerformanceLevel()];
    }

    /**
     * @return int Max file size allowed in backup
     */
    public function getMaxFileToBackup(): int
    {
        return $this::PERFORMANCE_VALUES['maxBackupFileSize'][$this->getPerformanceLevel()];
    }

    /**
     * @return int level of performance selected (0 for low, 2 for high)
     */
    public function getPerformanceLevel(): int
    {
        return $this->get('PS_AUTOUP_PERFORMANCE') - 1;
    }

    public function shouldBackupFilesAndDatabase(): bool
    {
        return (bool) $this->get('PS_AUTOUP_BACKUP');
    }

    /**
     * @return bool True if the autoupgrade module backup should include the images
     */
    public function shouldBackupImages(): bool
    {
        return (bool) $this->get('PS_AUTOUP_KEEP_IMAGES');
    }

    /**
     * @return bool True if non-native modules must be disabled during upgrade
     */
    public function shouldDeactivateCustomModules(): bool
    {
        return (bool) $this->get('PS_AUTOUP_CUSTOM_MOD_DESACT');
    }

    /**
     * @return bool true if we should keep the merchant emails untouched
     */
    public function shouldKeepMails(): bool
    {
        return (bool) $this->get('PS_AUTOUP_KEEP_MAILS');
    }

    /**
     * @return bool True if we have to set the native theme by default
     */
    public function shouldSwitchToDefaultTheme(): bool
    {
        return (bool) $this->get('PS_AUTOUP_CHANGE_DEFAULT_THEME');
    }

    /**
     * @return bool True if we are allowed to update the default theme files
     */
    public function shouldUpdateDefaultTheme(): bool
    {
        return (bool) $this->get('PS_AUTOUP_UPDATE_DEFAULT_THEME');
    }

    /**
     * @return bool True if we should update RTL files
     */
    public function shouldUpdateRTLFiles(): bool
    {
        return (bool) $this->get('PS_AUTOUP_UPDATE_RTL_FILES');
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
     */
    public function merge(array $array = []): void
    {
        foreach ($array as $key => $value) {
            $this->set($key, $value);
        }
    }
}
