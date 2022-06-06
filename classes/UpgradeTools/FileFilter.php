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
use PrestaShop\Module\AutoUpgrade\Tools14;

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
     * @var string Workspace directory
     */
    protected $workspaceDir;

    /**
     * @var string Current version
     */
    protected $currentVersion;

    /**
     * @var string Next version
     */
    protected $nextVersion;

    /**
     * @var array|null
     */
    protected $excludeAbsoluteFilesFromUpgrade;

    const COMPOSER_PACKAGE_TYPE = 'prestashop-module';

    const ADDITIONAL_ALLOWED_MODULES = [
        'autoupgrade',
    ];

    /**
     * @param UpgradeConfiguration $configuration
     * @param string $rootDir
     * @param string $workspaceDir
     * @param string $currentVersion
     * @param string $nextVersion
     * @param string $autoupgradeDir
     */
    public function __construct(
        UpgradeConfiguration $configuration,
        $rootDir,
        $workspaceDir,
        $currentVersion,
        $nextVersion,
        $autoupgradeDir = 'autoupgrade'
    ) {
        $this->configuration = $configuration;
        $this->rootDir = $rootDir;
        $this->workspaceDir = $workspaceDir;
        $this->currentVersion = $currentVersion;
        $this->nextVersion = $nextVersion;
        $this->autoupgradeDir = $autoupgradeDir;
    }

    /**
     * AdminSelfUpgrade::backupIgnoreAbsoluteFiles.
     *
     * @return array
     */
    public function getFilesToIgnoreOnBackup()
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
     * AdminSelfUpgrade::restoreIgnoreAbsoluteFiles.
     *
     * @return array
     */
    public function getFilesToIgnoreOnRestore()
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
     * AdminSelfUpgrade::excludeAbsoluteFilesFromUpgrade.
     *
     * @return array
     */
    public function getFilesToIgnoreOnUpgrade()
    {
        if ($this->excludeAbsoluteFilesFromUpgrade) {
            return $this->excludeAbsoluteFilesFromUpgrade;
        }

        // do not copy install, neither app/config/parameters.php in case it would be present
        $this->excludeAbsoluteFilesFromUpgrade = [
            '/app/config/parameters.php',
            '/app/config/parameters.yml',
            '/install',
            '/install-dev',
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
                if (in_array($fileinfo->getFilename(), $nativeModules)) {
                    $this->excludeAbsoluteFilesFromUpgrade[] = '/modules/' . $fileinfo->getFilename();
                }
            }
        }

        // this will exclude autoupgrade dir from admin, and autoupgrade from modules
        // If set to false, we need to preserve the default themes
        if (!$this->configuration->shouldUpdateDefaultTheme()) {
            $this->excludeAbsoluteFilesFromUpgrade[] = '/themes/classic';
        }

        return $this->excludeAbsoluteFilesFromUpgrade;
    }

    /**
     * AdminSelfUpgrade::backupIgnoreFiles
     * AdminSelfUpgrade::excludeFilesFromUpgrade
     * AdminSelfUpgrade::restoreIgnoreFiles.
     *
     * These files are checked in every subfolder of the directory tree and can match
     * several time, while the others are only matching a file from the project root.
     *
     * @return array
     */
    public function getExcludeFiles()
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
     * @return array<string>
     */
    private function getNativeModules()
    {
        return array_merge(
            // #1 : Source : Constant
            self::ADDITIONAL_ALLOWED_MODULES,
            // #2 : Source `composer.lock`
            $this->getNativeModulesFromComposerLock(),
            // #3 : External sources
            $this->getNativeModulesFromExternalSources()
        );
    }

    /**
     * @return array<string>
     */
    private function getNativeModulesFromComposerLock()
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

        return array_map(function (array $package) {
            $vendorName = explode('/', $package['name']);

            return $vendorName[1];
        }, $modules);
    }

    /**
     * @return array<string>
     */
    private function getNativeModulesFromExternalSources()
    {
        $configFile = $this->workspaceDir . DIRECTORY_SEPARATOR . 'config.json';
        // The file doesn't exist
        if (!is_file($configFile)) {
            return [];
        }
        $configContent = file_get_contents($configFile);
        $configContent = json_decode($configContent, true);
        // The file is empty
        if (empty($configContent)) {
            return [];
        }
        // The key `configuration` > `external` > `api` doesn't exist
        if (!isset($configContent['configuration']['external']['api'])
            || !is_array($configContent['configuration']['external']['api'])
        ) {
            return [];
        }

        $modules = [];
        foreach ($configContent['configuration']['external']['api'] as $externalApi) {
            if (!is_string($externalApi) || empty($externalApi)) {
                continue;
            }
            $externalApi = str_replace(
                [
                    '%version_current%',
                    '%version_upgrade%',
                ],
                [
                    $this->currentVersion,
                    $this->nextVersion,
                ],
                $externalApi
            );
            // Fetch external API
            $content = Tools14::file_get_contents($externalApi);
            $content = json_decode($content, true);
            if (empty($content) || !isset($content['native_modules']) || !is_array($content['native_modules'])) {
                continue;
            }
            // Add modules to the list
            foreach ($content['native_modules'] as $nativeModule) {
                if (!is_string($externalApi) || empty($externalApi)) {
                    continue;
                }
                $modules[] = $nativeModule;
            }
        }

        return $modules;
    }
}
