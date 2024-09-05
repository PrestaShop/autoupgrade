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

namespace PrestaShop\Module\AutoUpgrade\Services;

class ComposerService
{
    const COMPOSER_PACKAGE_TYPE = 'prestashop-module';

    /**
     * Returns packages defined as PrestaShop modules in composer.lock
     *
     * @return array<array{name:string, version:string}>
     */
    public function getModulesInComposerLock(string $composerFile): array
    {
        if (!file_exists($composerFile)) {
            return [];
        }
        // Native modules are the one integrated in PrestaShop release via composer
        // so we use the lock files to generate the list
        $content = file_get_contents($composerFile);
        $content = json_decode($content, true);
        if (empty($content['packages'])) {
            return [];
        }

        $modules = array_filter($content['packages'], function (array $package) {
            return self::COMPOSER_PACKAGE_TYPE === $package['type'] && !empty($package['name']);
        });
        $modules = array_map(function (array $package) {
            $vendorName = explode('/', $package['name']);

            return [
                'name' => $vendorName[1],
                'version' => ltrim($package['version'], 'v'),
            ];
        }, $modules);

        return $modules;
    }
}
