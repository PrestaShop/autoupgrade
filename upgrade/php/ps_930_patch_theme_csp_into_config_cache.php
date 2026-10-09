<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Backfill global_settings.csp into each theme's cached configuration, in place.
 *
 * Core reads config/themes/<theme>/shop<id>.json (and theme.json) in preference to the theme's theme.yml
 * and only regenerates it when missing, so a theme whose theme.yml gained a csp entry on upgrade — for 9.3
 * the Classic theme's global_settings.csp ('unsafe-eval'), which the storefront policy reads — would not
 * take effect on an existing shop.
 *
 * The cached file is NOT only a parsed copy of theme.yml: ThemeManager::saveTheme() also stores the
 * merchant's page-layout choices (theme_settings.layouts) there, so it must never be deleted. We copy
 * only global_settings.csp from the theme's current theme.yml into the JSON and leave everything else —
 * the layouts included — exactly as it was. Themes whose theme.yml has no csp entry (e.g. Hummingbird)
 * are skipped. A child theme that does not declare its own global_settings.csp is not patched: as
 * documented, such a child must repeat the parent's entry in its own theme.yml.
 *
 * @return void
 */
function ps_930_patch_theme_csp_into_config_cache()
{
    if (!defined('_PS_CONFIG_DIR_') || !defined('_PS_ALL_THEMES_DIR_') || !class_exists(Yaml::class)) {
        return;
    }

    $cachedFiles = glob(_PS_CONFIG_DIR_ . 'themes' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*.json');
    foreach ($cachedFiles ?: [] as $cachedFile) {
        patchThemeCspCacheFile($cachedFile);
    }
}

/**
 * Patch global_settings.csp from one theme's theme.yml into its cached config JSON, in place.
 * Themes with no csp entry, an unparseable theme.yml or an already-current cache are left untouched.
 *
 * @param string $cachedFile config/themes/<theme>/<file>.json
 *
 * @return void
 */
function patchThemeCspCacheFile($cachedFile)
{
    $themeName = basename(dirname($cachedFile));
    $themeYml = _PS_ALL_THEMES_DIR_ . $themeName . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'theme.yml';
    if (!is_file($themeYml)) {
        return;
    }

    try {
        $parsed = Yaml::parseFile($themeYml);
    } catch (ParseException $e) {
        return;
    }
    if (!is_array($parsed) || !isset($parsed['global_settings']['csp'])) {
        return;
    }

    $json = json_decode((string) file_get_contents($cachedFile), true);
    if (!is_array($json)) {
        return;
    }
    if (!isset($json['global_settings']) || !is_array($json['global_settings'])) {
        $json['global_settings'] = [];
    }
    // Already current (idempotent re-run): leave the file untouched.
    if (array_key_exists('csp', $json['global_settings']) && $json['global_settings']['csp'] === $parsed['global_settings']['csp']) {
        return;
    }

    // Copy only the csp key; the merchant's layouts and every other setting stay as they are.
    $json['global_settings']['csp'] = $parsed['global_settings']['csp'];
    file_put_contents($cachedFile, json_encode($json));
}
