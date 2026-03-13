<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
