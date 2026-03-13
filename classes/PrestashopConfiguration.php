<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade;

use Exception;
use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;
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

    public function fillInUpdateConfiguration(UpdateConfiguration $updateConfiguration): void
    {
        $updateConfiguration->merge([
            UpdateConfiguration::INSTALLED_LANGUAGES => $this->getInstalledLanguages(),
        ]);
    }
}
