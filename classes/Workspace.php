<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class Workspace
{
    /** @var Translator */
    private $translator;

    /**
     * @var string[] List of paths used by autoupgrade
     */
    private $paths;

    /** @var Filesystem */
    private $filesystem;

    /**
     * @param string[] $paths
     */
    public function __construct(Translator $translator, Filesystem $filesystem, array $paths)
    {
        $this->translator = $translator;
        $this->filesystem = $filesystem;
        $this->paths = $paths;
    }

    /**
     * @throws IOException
     */
    public function createFolders(): void
    {
        foreach ($this->paths as $path) {
            try {
                $this->filesystem->mkdir($path);
            } catch (IOException $e) {
                throw new IOException($this->translator->trans('Unable to create directory %s: %s', [$path, $e->getMessage()]));
            }

            if (!is_writable($path)) {
                throw new IOException($this->translator->trans('Cannot write to the directory. Please ensure you have the necessary write permissions on "%s".', [$path]));
            }

            $this->createPhpIndex($path);
        }
    }

    /**
     * @throws IOException
     */
    public function createPhpIndex(string $directoryPath): void
    {
        $filePath = $directoryPath . DIRECTORY_SEPARATOR . 'index.php';
        if (!$this->filesystem->exists($filePath)) {
            $this->filesystem->copy(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'index.php', $filePath);
        }
    }

    /**
     * @throws IOException
     */
    public function createHtAccess(string $modulePath): void
    {
        $filePath = $modulePath . DIRECTORY_SEPARATOR . '.htaccess';

        if (!$this->filesystem->exists($filePath)) {
            $content = <<<HTACCESS
<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{REQUEST_URI} ^.*/autoupgrade/logs/ [NC]
    RewriteCond %{REQUEST_URI} !\.txt$ [NC]
    RewriteRule ^ - [F]

    RewriteRule ^ - [L]
</IfModule>
HTACCESS;
            $this->filesystem->dumpFile($filePath, $content);
        }
    }

    /**
     * @throws IOException
     */
    public function init(string $modulePath): void
    {
        $this->createFolders();
        $this->createHtAccess($modulePath);
    }
}
