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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use DirectoryIterator;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Services\ComposerService;
use SplFileInfo;

class FileFilter
{
    /**
     * @var UpgradeConfiguration
     */
    protected $updateConfiguration;

    /** @var ComposerService */
    protected $composerService;

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
        UpgradeConfiguration $updateConfiguration,
        ComposerService $composerService,
        string $rootDir,
        string $autoupgradeDir = 'autoupgrade'
    ) {
        $this->updateConfiguration = $updateConfiguration;
        $this->composerService = $composerService;
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
        DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'cache',
        DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'smarty' . DIRECTORY_SEPARATOR . 'compile',
        DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'smarty' . DIRECTORY_SEPARATOR . 'cache',
        DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'tcpdf',
        DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'cachefs',
        DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache',

        // do not care about the two autoupgrade dir we use;
        DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'autoupgrade',
        DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'autoupgrade',
    ];

        if (!$this->updateConfiguration->shouldBackupImages()) {
            $backupIgnoreAbsoluteFiles[] = DIRECTORY_SEPARATOR . 'img';
        } else {
            $backupIgnoreAbsoluteFiles[] = DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'tmp';
        }

        return $backupIgnoreAbsoluteFiles;
    }

    /**
     * @return string[]
     */
    public function getFilesToIgnoreOnRestore(): array
    {
        $restoreIgnoreAbsoluteFiles = [
        DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'parameters.php',
        DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'parameters.yml',
        DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'autoupgrade',
        DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'autoupgrade',
        '.',
        '..',
    ];

        if (!$this->updateConfiguration->shouldBackupImages()) {
            $restoreIgnoreAbsoluteFiles[] = DIRECTORY_SEPARATOR . 'img';
        } else {
            $restoreIgnoreAbsoluteFiles[] = DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'tmp';
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
        DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'parameters.php',
        DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'parameters.yml',
        DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'c' . DIRECTORY_SEPARATOR . '*.jpg',
        DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR . '*.jpg',
        DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'l' . DIRECTORY_SEPARATOR . '*.jpg',
        DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'm' . DIRECTORY_SEPARATOR . '*.jpg',
        DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'os' . DIRECTORY_SEPARATOR . '*.jpg',
        DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'p' . DIRECTORY_SEPARATOR . '*.jpg',
        DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 's' . DIRECTORY_SEPARATOR . '*.jpg',
        DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'scenes' . DIRECTORY_SEPARATOR . '*.jpg',
        DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'st' . DIRECTORY_SEPARATOR . '*.jpg',
        DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'su' . DIRECTORY_SEPARATOR . '*.jpg',
        DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . '404.gif',
        DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'favicon.ico',
        DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'logo.jpg',
        DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'logo_stores.gif',
        DIRECTORY_SEPARATOR . 'install',
        DIRECTORY_SEPARATOR . 'install-dev',
        DIRECTORY_SEPARATOR . 'override',
        DIRECTORY_SEPARATOR . 'override' . DIRECTORY_SEPARATOR . 'classes',
        DIRECTORY_SEPARATOR . 'override' . DIRECTORY_SEPARATOR . 'controllers',
        DIRECTORY_SEPARATOR . 'override' . DIRECTORY_SEPARATOR . 'modules',
    ];

        // Fetch all existing native modules
        $nativeModules = array_column(
        $this->composerService->getModulesInComposerLock($this->rootDir . DIRECTORY_SEPARATOR . 'composer.lock'),
        'name'
    );

        if (is_dir($this->rootDir . DIRECTORY_SEPARATOR . 'modules')) {
            $dir = new DirectoryIterator($this->rootDir . DIRECTORY_SEPARATOR . 'modules');
            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                    continue;
                }
                if (!in_array($fileinfo->getFilename(), $nativeModules)) {
                    continue;
                }
                if (!(new SplFileInfo($this->rootDir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $fileinfo->getFilename() . DIRECTORY_SEPARATOR . 'vendor'))->isDir()) {
                    // If a vendor folder is found in the module, this means it has been upgraded or manually installed
                    // and can be ignored during the upgrade process
                    continue;
                }
                $this->excludeAbsoluteFilesFromUpgrade[] = DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $fileinfo->getFilename();
            }
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
}
