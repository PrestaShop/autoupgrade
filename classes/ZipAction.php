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

use PrestaShop\Module\AutoUpgrade\Exceptions\ZipActionException;
use PrestaShop\Module\AutoUpgrade\Log\LoggerInterface;
use PrestaShop\Module\AutoUpgrade\Parameters\UpgradeConfiguration;
use PrestaShop\Module\AutoUpgrade\Progress\Backlog;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

class ZipAction
{
    /**
     * @var int Number of files added in a zip per request
     */
    private $configMaxNbFilesCompressedInARow;
    /**
     * @var int Max file size allowed in a zip file
     */
    private $configMaxFileSizeAllowed;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string Path to the shop, in order to remove it from the archived file paths
     */
    private $prodRootDir;

    public function __construct(Filesystem $filesystem, Translator $translator, LoggerInterface $logger, UpgradeConfiguration $updateConfiguration, string $prodRootDir)
    {
        $this->filesystem = $filesystem;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->prodRootDir = $prodRootDir;
        $this->configMaxNbFilesCompressedInARow = $updateConfiguration->getNumberOfFilesPerCall();
        $this->configMaxFileSizeAllowed = $updateConfiguration->getMaxFileToBackup();
    }

    /**
     * Add files to an archive.
     * Note the number of files added can be limited.
     */
    public function compress(Backlog $backlog, string $toFile): bool
    {
        try {
            $zip = $this->open($toFile, ZipArchive::CREATE);
        } catch (ZipActionException $e) {
            return false;
        }

        for ($i = 0; $i < $this->configMaxNbFilesCompressedInARow && $backlog->getRemainingTotal(); ++$i) {
            $file = $backlog->getNext();

            $archiveFilename = $this->getFilepathInArchive($file);
            if (!$this->isFileWithinFileSizeLimit($file)) {
                continue;
            }

            if (is_link($file)) {
                $result = $this->addSymlink($file, $archiveFilename, $zip);
            } else {
                $result = $zip->addFile($file, $archiveFilename);
            }

            if (!$result) {
                $error = $zip->getStatusString();
                // if an error occur, it's more safe to delete the corrupted backup
                $zip->close();
                $this->filesystem->remove($toFile);
                $this->logger->error($this->translator->trans(
                    'Unable to add %filename% to archive %archive%: %error%',
                    [
                        '%filename%' => $file,
                        '%archive%' => $archiveFilename,
                        '%error%' => $error,
                    ]
                ));

                return false;
            }

            $this->logger->debug($this->translator->trans(
                '%filename% added to archive. %filescount% files left.',
                [
                    '%filename%' => $archiveFilename,
                    '%filescount%' => $backlog->getRemainingTotal(),
                ]
            ));
        }

        if (!$zip->close()) {
            $error = $zip->getStatusString();

            $this->logger->error($this->translator->trans(
                'Could not close the Zip file %toFile%: %error%',
                [
                    '%toFile%' => $toFile,
                    '%error%' => $error,
                ]
            ));

            return false;
        }

        return true;
    }

    private function addSymlink(string $file, string $archiveFilename, ZipArchive $zip): bool
    {
        $targetPath = readlink($file);
        $stat = lstat($file);

        return $zip->addFromString($archiveFilename, $targetPath)
            && $zip->setExternalAttributesName($archiveFilename,
                ZipArchive::OPSYS_UNIX,
                $stat['mode'] << 16
            );
    }

    /**
     * Extract an archive to the given directory
     *
     * @return bool success
     *              we need a copy of it to be able to restore without keeping Tools and Autoload stuff
     */
    public function extract(string $from_file, string $to_dir): bool
    {
        if (!is_file($from_file)) {
            $this->logger->error($this->translator->trans('%s is not a file', [$from_file]));

            return false;
        }

        if (!$this->filesystem->exists($to_dir)) {
            try {
                $this->filesystem->mkdir($to_dir, 0775);
            } catch (IOException $exception) {
                $this->logger->error($this->translator->trans('Unable to create directory %s.', [$to_dir]));

                return false;
            }
        }

        try {
            $zip = $this->open($from_file);
        } catch (ZipActionException $e) {
            return false;
        }

        /** @var int[] */
        $deferredLinks = [];

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $nameIndex = $zip->getNameIndex($i);
            $zip->getExternalAttributesName($nameIndex, $opsys, $stat);

            if ($opsys === ZipArchive::OPSYS_UNIX && $this->isCompressedFileASymLink($stat, $nameIndex)) {
                $deferredLinks[] = $i;
            } elseif (!$zip->extractTo($to_dir, [$nameIndex])) {
                $issue = $zip->getStatusString() ?: $this->translator->trans('Unknown extraction error (possible permission issue or filesystem error)');

                $fullDiskPath = rtrim($to_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nameIndex;
                $isFileUnchanged = $this->isFileUnchanged($fullDiskPath, $nameIndex, $zip);

                $nonCriticalIssueNotice = $isFileUnchanged ? $this->translator->trans(', but ignoring as source and destination appear identical') : '';

                $this->logger->warning(
                    $this->translator->trans(
                        'Could not extract %file% from archive%nonCriticalIssueNotice%: %$issue%.',
                        [
                            '%file%' => $nameIndex,
                            '%nonCriticalIssueNotice%' => $nonCriticalIssueNotice,
                            '%$issue%' => $issue,
                        ]
                    )
                );

                if (!$isFileUnchanged) {
                    $zip->close();

                    return false;
                }
            }
        }

        foreach ($deferredLinks as $i) {
            $nameIndex = $zip->getNameIndex($i);

            try {
                $this->filesystem->symlink($zip->getFromIndex($i), $to_dir . DIRECTORY_SEPARATOR . $nameIndex);
            } catch (IOException $e) {
                $this->logger->warning(
                    $this->translator->trans(
                        'Could not extract link %file% from archive: %error%',
                        [
                            '%file%' => $nameIndex,
                            '%error%' => $e->getMessage(),
                        ]
                    )
                );
                $zip->close();

                return false;
            }
        }

        $zip->close();
        $this->logger->debug($this->translator->trans('Content of archive %zip% is extracted', ['%zip%' => $from_file]));

        return true;
    }

    /**
     * Lists the files present in the given archive
     *
     * @param string $zipFile Path to the file
     *
     * @return string[]
     */
    public function listContent(string $zipFile): array
    {
        if (!$this->filesystem->exists($zipFile)) {
            return [];
        }

        try {
            $zip = $this->open($zipFile);
        } catch (ZipActionException $e) {
            $this->logger->error($this->translator->trans('Unable to list archived files'));

            return [];
        }

        $files = [];
        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $files[] = $zip->getNameIndex($i);
        }

        return $files;
    }

    /**
     * Open an archive
     *
     * @param string $zipFile Path to the archive
     * @param int|null $flags ZipArchive flags
     *
     * @throws ZipActionException
     */
    public function open(string $zipFile, ?int $flags = null): ZipArchive
    {
        $zip = new ZipArchive();
        if (null === $flags) {
            $flags = 0;
        }
        if ($zip->open($zipFile, $flags) !== true || empty($zip->filename)) {
            $error = $zip->getStatusString();

            $this->logger->error($this->translator->trans('Unable to open archive %s: %s', [$zipFile, $error]));
            throw new ZipActionException('Unable to open archive ' . $zipFile . ': ' . $error);
        }

        return $zip;
    }

    /**
     * @throws ZipActionException
     */
    public function extractFileFromArchive(ZipArchive $zip, string $fileName): string
    {
        $fileIndex = $zip->locateName($fileName);
        if ($fileIndex !== false) {
            return $zip->getFromIndex($fileIndex);
        }
        throw new ZipActionException("Unable to find $fileName file");
    }

    /**
     * Determines whether a file on disk is identical to its counterpart in a ZIP archive.
     *
     * This method compares the MD5 hash of the file content extracted from the ZIP archive
     * with the MD5 hash of the file located on the given disk path. If the file does not exist
     * on disk, the comparison will return false.
     *
     * @param string $diskFilePath absolute path to the file on disk
     * @param string $nameIndex the name of the file inside the ZIP archive
     * @param ZipArchive $zip the ZipArchive instance containing the file to compare
     *
     * @return bool returns true if the file on disk is unchanged (identical content),
     *              false if it is different or does not exist
     */
    public function isFileUnchanged(string $diskFilePath, string $nameIndex, ZipArchive $zip): bool
    {
        $zipFileContent = $zip->getFromName($nameIndex);
        $md5FromZip = md5($zipFileContent);
        $md5FromDisk = is_file($diskFilePath) ? md5_file($diskFilePath) : null;

        return $md5FromZip === $md5FromDisk;
    }

    /**
     * Get the path of a file from the archive root
     *
     * @param string $filepath Path of the file on the filesystem
     *
     * @return string Path of the file in the backup archive
     */
    private function getFilepathInArchive(string $filepath): string
    {
        return ltrim(str_replace($this->prodRootDir, '', $filepath), DIRECTORY_SEPARATOR);
    }

    /**
     * Checks a file size matches the given limits
     *
     * @param string $filepath Path to a file
     *
     * @return bool Size is inside the maximum limit
     */
    private function isFileWithinFileSizeLimit(string $filepath): bool
    {
        $size = filesize($filepath);
        $pass = ($size < $this->configMaxFileSizeAllowed);
        if (!$pass) {
            $this->logger->debug($this->translator->trans(
                'File %filename% (size: %filesize%) has been skipped during backup.',
                [
                    '%filename%' => $this->getFilepathInArchive($filepath),
                    '%filesize%' => $size,
                ]
            ));
        }

        return $pass;
    }

    /*
     * Detect files stored as symlinks. Matches octal value 12000 and does not end with a separator
     * @see https://discuss.python.org/t/how-info-zip-represents-symlinks/4104/2
     * @see https://pkware.cachefly.net/webdocs/casestudies/APPNOTE.TXT - 4.5.7 -UNIX Extra Field (0x000d):
     */
    public function isCompressedFileASymLink(int $stat, string $file): bool
    {
        return (($stat >> 16) & 0120000) === 0120000 && !in_array(substr($file, -1), ['/', '\\']);
    }
}
