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

use PrestaShop\Module\AutoUpgrade\Tools14;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class used for management of to do files for upgrade tasks.
 * Load / Save / Delete etc.
 */
class FileConfigurationStorage
{
    /**
     * @var string Location where all the configuration files are stored
     */
    private $configPath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(string $path)
    {
        $this->configPath = $path;
        $this->filesystem = new Filesystem();
    }

    /**
     * UpgradeConfiguration loader.
     *
     * @param string $fileName File name to load
     *
     * @return mixed or array() as default value
     */
    public function load(string $fileName = '')
    {
        $configFilePath = $this->configPath . $fileName;
        $config = [];

        if (file_exists($configFilePath)) {
            $config = @unserialize(base64_decode(Tools14::file_get_contents($configFilePath)));
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
    public function getFilesList(): array
    {
        $files = [];
        foreach (UpgradeFileNames::$tmp_files as $file) {
            $files[$file] = $this->getFilePath(constant('PrestaShop\\Module\\AutoUpgrade\\Parameters\\UpgradeFileNames::' . $file));
        }

        return $files;
    }

    /**
     * Delete all temporary files in the config folder.
     */
    public function cleanAll(): void
    {
        $this->filesystem->remove(self::getFilesList());
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
