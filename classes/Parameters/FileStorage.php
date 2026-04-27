<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class used for management of to do files for upgrade tasks.
 * Load / Save / Delete etc.
 */
class FileStorage
{
    /**
     * @var string Location where all the configuration files are stored
     */
    private $configPath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem, string $path)
    {
        $this->filesystem = $filesystem;
        $this->configPath = $path;
    }

    /**
     * Configuration loader.
     *
     * @param string $fileName File name to load
     *
     * @return mixed or array() as default value
     */
    public function load(string $fileName = '')
    {
        $configFilePath = $this->configPath . $fileName;
        $config = [];

        if ($this->filesystem->exists($configFilePath)) {
            $config = @unserialize(base64_decode(file_get_contents($configFilePath)));
        }

        return $config;
    }

    /**
     * @param mixed $config
     * @param string $fileName Destination name of the config file
     */
    public function save($config, string $fileName): bool
    {
        $configFilePath = $this->configPath . $fileName;
        try {
            $this->filesystem->dumpFile($configFilePath, base64_encode(serialize($config)));

            return true;
        } catch (IOException $e) {
            // TODO: $e needs to be logged
            return false;
        }
    }

    /**
     * @return array<string, string> Temporary files path & name
     */
    public function getUpdateFilesList(): array
    {
        $files = [];
        foreach (UpgradeFileNames::$update_tmp_files as $key => $file) {
            $files[$key] = $this->getFilePath($file);
        }

        return $files;
    }

    /**
     * @return array<string, string> Temporary files path & name
     */
    public function getBackupFilesList(): array
    {
        $files = [];
        foreach (UpgradeFileNames::$backup_tmp_files as $key => $file) {
            $files[$key] = $this->getFilePath($file);
        }

        return $files;
    }

    /**
     * @return array<string, string> Temporary files path & name
     */
    public function getRestoreFilesList(): array
    {
        $files = [];
        foreach (UpgradeFileNames::$restore_tmp_files as $key => $file) {
            $files[$key] = $this->getFilePath($file);
        }

        return $files;
    }

    /**
     * Delete all temporary files in the config folder for update process.
     */
    public function cleanAllUpdateFiles(): void
    {
        $this->filesystem->remove(self::getUpdateFilesList());
    }

    /**
     * Delete all temporary files in the config folder for backup process.
     */
    public function cleanAllBackupFiles(): void
    {
        $this->filesystem->remove(self::getBackupFilesList());
    }

    /**
     * Delete all temporary files in the config folder for restore process.
     */
    public function cleanAllRestoreFiles(): void
    {
        $this->filesystem->remove(self::getRestoreFilesList());
    }

    /**
     * Delete a file from the filesystem.
     *
     * @param string $fileName
     */
    public function clean(string $fileName): void
    {
        $this->filesystem->remove($this->getFilePath($fileName));
    }

    /**
     * Check if a file exists on the filesystem.
     *
     * @param string $fileName
     */
    public function exists(string $fileName): bool
    {
        return $this->filesystem->exists($this->getFilePath($fileName));
    }

    /**
     * Generate the complete path to a given file.
     */
    private function getFilePath(string $file): string
    {
        return $this->configPath . $file;
    }
}
