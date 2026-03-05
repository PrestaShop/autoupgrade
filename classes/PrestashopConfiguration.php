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

namespace PrestaShop\Module\AutoUpgrade;

use Exception;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleAdapter;
use Symfony\Component\Filesystem\Filesystem;

class PrestashopConfiguration
{
    // Variables used for cache
    /** @var string */
    private $moduleVersion;

    // Variables from main class
    /** @var Filesystem */
    private $filesystem;
    /** @var ModuleAdapter */
    private $moduleAdapter;
    /** @var string */
    private $psRootDir;

    public function __construct(Filesystem $filesystem, ModuleAdapter $moduleAdapter, string $psRootDir)
    {
        $this->filesystem = $filesystem;
        $this->moduleAdapter = $moduleAdapter;
        $this->psRootDir = $psRootDir;
    }

    /**
     * Returns the module version if found in the config.xml file, null otherwise.
     */
    public function getModuleVersion(): ?string
    {
        if (null !== $this->moduleVersion) {
            return $this->moduleVersion;
        }

        // TODO: to be moved as property class in order to make tests possible
        $path = _PS_ROOT_DIR_ . '/modules/autoupgrade';

        $this->moduleVersion = $this->moduleAdapter->getVersionFromConfigXmlInFolder($path);

        return $this->moduleVersion;
    }

    /**
     * @throws Exception
     */
    public function getPrestaShopVersion(): string
    {
        if (defined('_PS_VERSION_')) {
            return _PS_VERSION_;
        }
        $files = [
            $this->psRootDir . '/config/settings.inc.php',
            $this->psRootDir . '/config/autoload.php',
            $this->psRootDir . '/app/AppKernel.php',
            $this->psRootDir . '/src/Core/Version.php',
        ];
        foreach ($files as $file) {
            if (!$this->filesystem->exists($file)) {
                continue;
            }
            $version = $this->findPrestaShopVersionInFile(file_get_contents($file));
            if ($version) {
                return $version;
            }
        }

        throw new Exception('Can\'t find PrestaShop Version');
    }

    /**
     * @param string $content File content
     *
     * @internal Used for test
     */
    public function findPrestaShopVersionInFile(string $content): ?string
    {
        $matches = [];
        // Example: define('_PS_VERSION_', '1.7.3.4');
        if (1 === preg_match("/define\([\"']_PS_VERSION_[\"'], [\"'](?<version>[0-9.]+)[\"']\)/", $content, $matches)) {
            return $matches['version'];
        }

        // Example: const VERSION = '1.7.6.0';
        if (1 === preg_match("/const VERSION = [\"'](?<version>[0-9.]+)[\"'];/", $content, $matches)) {
            return $matches['version'];
        }

        return null;
    }

    /**
     * Rely on installed languages to merge translations files
     *
     * @return string[]
     */
    public function getInstalledLanguages(): array
    {
        return array_map(
            function ($v) { return $v['iso_code']; },
            \Language::getIsoIds(false)
        );
    }

    public function fillInUpdateConfiguration(UpgradeConfiguration $upgradeConfiguration): void
    {
        $upgradeConfiguration->merge([
            UpgradeConfiguration::INSTALLED_LANGUAGES => $this->getInstalledLanguages(),
        ]);
    }
}
