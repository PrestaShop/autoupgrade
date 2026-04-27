<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Services;

use PrestaShop\Module\AutoUpgrade\Exceptions\ZipActionException;
use PrestaShop\Module\AutoUpgrade\ZipAction;
use RuntimeException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class PrestashopVersionService
{
    /**
     * @var ZipAction
     */
    private $zipAction;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(ZipAction $zipAction, Filesystem $filesystem)
    {
        $this->zipAction = $zipAction;
        $this->filesystem = $filesystem;
    }

    /**
     * @throws ZipActionException
     * @throws FileNotFoundException
     */
    public function extractPrestashopVersionFromZip(string $zipFile): string
    {
        $internalZipFileName = 'prestashop.zip';
        $versionFile = 'install/install_version.php';

        if (!$this->filesystem->exists($zipFile)) {
            throw new FileNotFoundException("Unable to find $zipFile file");
        }
        $zip = $this->zipAction->open($zipFile);
        $internalZipContent = $this->zipAction->extractFileFromArchive($zip, $internalZipFileName);
        $zip->close();

        $tempInternalZipPath = $this->createTemporaryFile($internalZipContent);

        $internalZip = $this->zipAction->open($tempInternalZipPath);
        $fileContent = $this->zipAction->extractFileFromArchive($internalZip, $versionFile);
        $internalZip->close();

        $this->filesystem->remove($tempInternalZipPath);

        return $this->extractVersionFromContent($fileContent);
    }

    /**
     * @throws RuntimeException
     */
    public function extractPrestashopVersionFromXml(string $xmlPath): string
    {
        $xml = @simplexml_load_file($xmlPath);

        if (!isset($xml->ps_root_dir['version'])) {
            throw new RuntimeException('Prestashop version not found in file ' . $xmlPath);
        }

        return $xml->ps_root_dir['version'];
    }

    /**
     * @throws IOException
     */
    private function createTemporaryFile(string $content): string
    {
        $tempFilePath = $this->filesystem->tempnam(sys_get_temp_dir(), 'internal_zip_');
        $this->filesystem->appendToFile($tempFilePath, $content);

        return $tempFilePath;
    }

    /**
     * @throws RuntimeException
     */
    private function extractVersionFromContent(string $content): string
    {
        $pattern = "/define\\('_PS_INSTALL_VERSION_', '([\\d.]+)'\\);/";
        if (preg_match($pattern, $content, $matches)) {
            return $matches[1];
        } else {
            throw new RuntimeException('Unable to extract version from content');
        }
    }
}
