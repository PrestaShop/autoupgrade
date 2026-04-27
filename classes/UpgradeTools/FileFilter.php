<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools;

use DirectoryIterator;
use PrestaShop\Module\AutoUpgrade\Parameters\BackupConfiguration;
use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;
use PrestaShop\Module\AutoUpgrade\Services\ComposerService;
use SplFileInfo;

class FileFilter
{
    /**
     * @var UpdateConfiguration
     */
    protected $updateConfiguration;

    /** @var BackupConfiguration */
    protected $backupConfiguration;

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
        UpdateConfiguration $updateConfiguration,
        BackupConfiguration $backupConfiguration,
        ComposerService $composerService,
        string $rootDir,
        string $autoupgradeDir = 'autoupgrade'
    ) {
        $this->updateConfiguration = $updateConfiguration;
        $this->backupConfiguration = $backupConfiguration;
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

        if (!$this->backupConfiguration->shouldBackupImages()) {
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

        // TODO: Let the images being overwritten by the backup if they exist.
        // For the images created after the backup, they will remain of the filesystem until
        // we find a condition based on the presence of the images in the backup.
        $restoreIgnoreAbsoluteFiles[] = '/img';

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
            '/override',
            '/override/classes',
            '/override/controllers',
            '/override/modules',
        ];

        // Fetch all existing native modules
        $nativeModules = array_column(
            $this->composerService->getModulesInComposerLock($this->rootDir . '/composer.lock'),
            'name'
        );

        if (is_dir($this->rootDir . '/modules')) {
            $dir = new DirectoryIterator($this->rootDir . '/modules');
            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                    continue;
                }
                if (in_array($fileinfo->getFilename(), $nativeModules) && !(new SplFileInfo($this->rootDir . '/modules/' . $fileinfo->getFilename() . '/vendor'))->isDir()) {
                    // If a vendor folder is found in the module, this means it has been upgraded or manually installed
                    // and can be ignored during the upgrade process
                    continue;
                }
                $this->excludeAbsoluteFilesFromUpgrade[] = '/modules/' . $fileinfo->getFilename();
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
