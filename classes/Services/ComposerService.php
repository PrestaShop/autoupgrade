<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Services;

use Symfony\Component\Filesystem\Filesystem;

class ComposerService
{
    const COMPOSER_PACKAGE_TYPE = 'prestashop-module';

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Returns packages defined as PrestaShop modules in composer.lock
     *
     * @return array<array{name:string, version:string}>
     */
    public function getModulesInComposerLock(string $composerFile): array
    {
        if (!$this->filesystem->exists($composerFile)) {
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

            return [
                'name' => $vendorName[1],
                'version' => ltrim($package['version'], 'v'),
            ];
        }, $modules);
    }
}
