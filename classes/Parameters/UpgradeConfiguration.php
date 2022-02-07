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

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Contains the module configuration (form params).
 */
class UpgradeConfiguration extends ArrayCollection
{
    /**
     * Performance settings, if your server has a low memory size, lower these values.
     *
     * @var array
     */
    protected $performanceValues = [
        'loopFiles' => [400, 800, 1600], // files
        'loopTime' => [6, 12, 25], // seconds
        'maxBackupFileSize' => [15728640, 31457280, 62914560], // bytes
        'maxWrittenAllowed' => [4194304, 8388608, 16777216], // bytes
    ];

    /**
     * Get the name of the new release archive.
     *
     * @return string
     */
    public function getArchiveFilename()
    {
        return $this->get('archive.filename');
    }

    /**
     * Get the version included in the new release.
     *
     * @return string
     */
    public function getArchiveVersion()
    {
        return $this->get('archive.version_num');
    }

    /**
     * Get channel selected on config panel (Minor, major ...).
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->get('channel');
    }

    /**
     * @return int Number of files to handle in a single call to avoid timeouts
     */
    public function getNumberOfFilesPerCall()
    {
        return $this->performanceValues['loopFiles'][$this->getPerformanceLevel()];
    }

    /**
     * @return int Number of seconds allowed before having to make another request
     */
    public function getTimePerCall()
    {
        return $this->performanceValues['loopTime'][$this->getPerformanceLevel()];
    }

    /**
     * @return int Kind of reference for SQL file creation, giving a file size before another request is needed
     */
    public function getMaxSizeToWritePerCall()
    {
        return $this->performanceValues['maxWrittenAllowed'][$this->getPerformanceLevel()];
    }

    /**
     * @return int Max file size allowed in backup
     */
    public function getMaxFileToBackup()
    {
        return $this->performanceValues['maxBackupFileSize'][$this->getPerformanceLevel()];
    }

    /**
     * @return int level of performance selected (0 for low, 2 for high)
     */
    public function getPerformanceLevel()
    {
        return $this->get('PS_AUTOUP_PERFORMANCE') - 1;
    }

    /**
     * @return bool True if the autoupgrade module should backup the images as well
     */
    public function shouldBackupImages()
    {
        return (bool) $this->get('PS_AUTOUP_KEEP_IMAGES');
    }

    /**
     * @return bool True if non-native modules must be disabled during upgrade
     */
    public function shouldDeactivateCustomModules()
    {
        return (bool) $this->get('PS_AUTOUP_CUSTOM_MOD_DESACT');
    }

    /**
     * @return bool true if we should keep the merchant emails untouched
     */
    public function shouldKeepMails()
    {
        return (bool) $this->get('PS_AUTOUP_KEEP_MAILS');
    }

    /**
     * @return bool True if we have to set the native theme by default
     */
    public function shouldSwitchToDefaultTheme()
    {
        return (bool) $this->get('PS_AUTOUP_CHANGE_DEFAULT_THEME');
    }

    /**
     * @return bool True if we are allowed to update the default theme files
     */
    public function shouldUpdateDefaultTheme()
    {
        return (bool) $this->get('PS_AUTOUP_UPDATE_DEFAULT_THEME');
    }

    /**
     * @return bool True if we should update RTL files
     */
    public function shouldUpdateRTLFiles()
    {
        return (bool) $this->get('PS_AUTOUP_UPDATE_RTL_FILES');
    }

    public function merge(array $array = [])
    {
        foreach ($array as $key => $value) {
            $this->set($key, $value);
        }
    }
}
