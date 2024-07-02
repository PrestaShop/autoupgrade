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

use DirectoryIterator;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use SplFileInfo;

class FileFilter
{
    /**
     * @var UpgradeConfiguration
     */
    protected $configuration;

    /**
     * @var string Autoupgrade sub directory
     */
    protected $autoupgradeDir;

    /**
     * @var string Root directory
     */
    protected $rootDir;

    /**
     * @var string[]
     */
    protected $excludeAbsoluteFilesFromUpgrade;

    const COMPOSER_PACKAGE_TYPE = 'prestashop-module';

    const ADDITIONAL_ALLOWED_MODULES = [
        'autoupgrade',
    ];

    public function __construct(
        UpgradeConfiguration $configuration,
        string $rootDir,
        string $autoupgradeDir = 'autoupgrade'
    ) {
        $this->configuration = $configuration;
        $this->rootDir = $rootDir;
        $this->autoupgradeDir = $autoupgradeDir;
    }

    /**
     * @return string[]
     */
    public function getFilesToIgnoreOnBackup(): array
    {
        // during backup, do not save
        $backupIgnoreAbsoluteFiles = [
            '/app/cache',
            '/cache/smarty/compile',
            '/cache/smarty/cache',
            '/cache/tcpdf',
            '/cache/cachefs',
            '/var/cache',

            // do not care about the two autoupgrade dir we use;
            '/modules/autoupgrade',
            '/admin/autoupgrade',
        ];

        if (!$this->configuration->shouldBackupImages()) {
            $backupIgnoreAbsoluteFiles[] = '/img';
        } else {
            $backupIgnoreAbsoluteFiles[] = '/img/tmp';
        }

        return $backupIgnoreAbsoluteFiles;
    }

    /**
     * @return string[]
     */
    public function getFilesToIgnoreOnRestore(): array
    {
        $restoreIgnoreAbsoluteFiles = [
            '/app/config/parameters.php',
            '/app/config/parameters.yml',
            '/modules/autoupgrade',
            '/admin/autoupgrade',
            '.',
            '..',
        ];

        if (!$this->configuration->shouldBackupImages()) {
            $restoreIgnoreAbsoluteFiles[] = '/img';
        } else {
            $restoreIgnoreAbsoluteFiles[] = '/img/tmp';
        }

        return $restoreIgnoreAbsoluteFiles;
    }

    /**
     * @return string[]
     */
    public function getFilesToIgnoreOnUpgrade(): array
    {
        if ($this->excludeAbsoluteFilesFromUpgrade) {
            return $this->excludeAbsoluteFilesFromUpgrade;
        }

        $this->excludeAbsoluteFilesFromUpgrade = [
            '/app/config/parameters.php',
            '/app/config/parameters.yml',
            '/img/c/*.jpg',
            '/img/cms/*.jpg',
            '/img/l/*.jpg',
            '/img/m/*.jpg',
            '/img/os/*.jpg',
            '/img/p/*.jpg',
            '/img/s/*.jpg',
            '/img/scenes/*.jpg',
            '/img/st/*.jpg',
            '/img/su/*.jpg',
            '/img/404.gif',
            '/img/favicon.ico',
            '/img/logo.jpg',
            '/img/logo_stores.gif',
            '/install',
            '/install-dev',
            // TODO: Uncomment when a better management of modules upgrades is implemented
            // '/modules',
            '/override',
            '/override/classes',
            '/override/controllers',
            '/override/modules',
        ];

        // Fetch all existing native modules
        $nativeModules = $this->getNativeModules();

        if (is_dir($this->rootDir . '/modules')) {
            $dir = new DirectoryIterator($this->rootDir . '/modules');
            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                    continue;
                }
                if (!in_array($fileinfo->getFilename(), $nativeModules)) {
                    continue;
                }
                if (!(new SplFileInfo($this->rootDir . '/modules/' . $fileinfo->getFilename() . '/vendor'))->isDir()) {
                    // If a vendor folder is found in the module, this means it has been upgraded or manually installed
                    // and can be ignored during the upgrade process
                    continue;
                }
                $this->excludeAbsoluteFilesFromUpgrade[] = '/modules/' . $fileinfo->getFilename();
            }
        }

        // If set to false, we need to preserve the default themes
        if (!$this->configuration->shouldUpdateDefaultTheme()) {
            $this->excludeAbsoluteFilesFromUpgrade[] = '/themes/classic';
        }

        return $this->excludeAbsoluteFilesFromUpgrade;
    }

    /**
     * These files are checked in every subfolder of the directory tree and can match
     * several time, while the others are only matching a file from the project root.
     *
     * @return string[]
     */
    public function getExcludeFiles(): array
    {
        return [
            '.',
            '..',
            '.svn',
            '.git',
            $this->autoupgradeDir,
        ];
    }

    /**
     * Returns an array of native modules
     *
     * @return string[]
     */
    private function getNativeModules(): array
    {
        $composerFile = $this->rootDir . '/composer.lock';
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

            return $vendorName[1];
        }, $modules);

        return array_merge(
            $modules,
            self::ADDITIONAL_ALLOWED_MODULES
        );
    }
}
