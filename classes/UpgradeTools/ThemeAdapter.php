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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use Db;

class ThemeAdapter
{
    /** @var Db */
    private $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    /**
     * Get the default theme name provided with PrestaShop.
     *
     * @return string
     */
    public function getDefaultTheme(): string
    {
        return 'classic';
    }

    /**
     * Get the list of theme name.
     *
     * @return array{array{'name':string, 'directory':string}}
     */
    public function getListFromDisk(): array
    {
        $suffix = 'config/theme.yml';
        $themeDirectories = glob(_PS_ALL_THEMES_DIR_ . '*/' . $suffix, GLOB_NOSORT);

        $themes = [];
        foreach ($themeDirectories as $directory) {
            $themes[] = [
                'name' => basename(substr($directory, 0, -strlen($suffix))),
                'directory' => substr($directory, 0, -strlen($suffix)),
            ];
        }

        return $themes;
    }

    /**
     * Use theme manager is order to enable the new theme.
     *
     * @param string $themeName
     *
     * @return bool|string True on success, string with errors on failure
     */
    public function enableTheme(string $themeName)
    {
        // Load up core theme manager
        $themeManager = $this->getThemeManager();

        // Enable the theme
        $isThemeEnabled = $themeManager->enable($themeName);
        if (!$isThemeEnabled) {
            // Something went wrong... let's check if we have some more info
            $errors = $themeManager->getErrors($themeName);
            if (is_array($errors) && !empty($errors)) {
                return implode(',', $errors);
            }

            return 'Unknown error';
        }

        return true;
    }

    /**
     * @return \PrestaShop\PrestaShop\Core\Addon\Theme\ThemeManager
     */
    private function getThemeManager()
    {
        return (new \PrestaShop\PrestaShop\Core\Addon\Theme\ThemeManagerBuilder(\Context::getContext(), $this->db))->build();
    }
}
