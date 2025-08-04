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

namespace PrestaShop\Module\AutoUpgrade\Xml;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter;
use SimpleXMLElement;

class ChecksumCompare
{
    public const CATEGORY_MAIL = 'mail';
    public const CATEGORY_TRANSLATION = 'translation';
    public const CATEGORY_CORE = 'core';
    public const CATEGORY_THEME = 'themes';

    public const FILE_MISSING = 'missing';
    public const FILE_ALTERED = 'altered';
    /**
     * @var FileLoader
     */
    private $fileLoader;
    /**
     * @var FilesystemAdapter
     */
    private $filesystemAdapter;
    /**
     * @var string
     */
    private $prodPath;
    /**
     * @var string
     */
    private $adminPath;

    /**
     * @var array{
     *             'mail':array{'missing':string[],'altered':string[]},
     *             'translation':array{'missing':string[],'altered':string[]},
     *             'core':array{'missing':string[],'altered':string[]},
     *             'themes':array{'missing':string[],'altered':string[]}
     *             }|false
     */
    private $fileDifferences = [
        self::CATEGORY_MAIL => [
            self::FILE_MISSING => [],
            self::FILE_ALTERED => [],
        ],
        self::CATEGORY_TRANSLATION => [
            self::FILE_MISSING => [],
            self::FILE_ALTERED => [],
        ],
        self::CATEGORY_CORE => [
            self::FILE_MISSING => [],
            self::FILE_ALTERED => [],
        ],
        self::CATEGORY_THEME => [
            self::FILE_MISSING => [],
            self::FILE_ALTERED => [],
        ],
    ];

    public function __construct(FileLoader $fileLoader, FilesystemAdapter $filesystemAdapter, string $prodPath, string $adminPath)
    {
        $this->fileLoader = $fileLoader;
        $this->filesystemAdapter = $filesystemAdapter;
        $this->prodPath = $prodPath;
        $this->adminPath = $adminPath;
    }

    /**
     * @return false|array{'modified': string[], "deleted": string[]}
     */
    public function getFilesDiffBetweenVersions(string $version1, ?string $version2)
    {
        $checksum1 = $this->fileLoader->getXmlMd5File($version1);
        $checksum2 = $this->fileLoader->getXmlMd5File($version2);
        if ($checksum1) {
            $v1 = $this->md5FileAsArray($checksum1->ps_root_dir[0]);
        }
        if ($checksum2) {
            $v2 = $this->md5FileAsArray($checksum2->ps_root_dir[0]);
        }
        if (empty($v1) || empty($v2)) {
            return false;
        }

        return $this->compareReleases($v1, $v2);
    }

    /**
     * returns an array of files which are present in PrestaShop version $version and has been modified
     * in the current filesystem.
     *
     * @return array{
     *                'mail':array{'missing':string[],'altered':string[]},
     *                'translation':array{'missing':string[],'altered':string[]},
     *                'core':array{'missing':string[],'altered':string[]},
     *                'themes':array{'missing':string[],'altered':string[]}
     *                }|false
     */
    public function getTamperedFilesOnShop(string $version)
    {
        if (is_array($this->fileDifferences)) {
            $useCache = false;

            foreach ($this->fileDifferences as $section) {
                foreach ([self::FILE_MISSING, self::FILE_ALTERED] as $key) {
                    if (!empty($section[$key])) {
                        $useCache = true;
                    }
                }
            }

            if ($useCache) {
                return $this->fileDifferences;
            }

            $checksum = $this->fileLoader->getXmlMd5File($version);
            if (!$checksum) {
                $this->fileDifferences = false;
            } else {
                $this->browseXmlAndCompare($checksum->ps_root_dir[0]);
            }
        }

        return $this->fileDifferences;
    }

    public function isAuthenticPrestashopVersion(string $version): bool
    {
        return !$this->getTamperedFilesOnShop($version);
    }

    /**
     * returns an array of files which.
     *
     * @param array<string, string|mixed[]> $v1 result of method $this->md5FileAsArray()
     * @param array<string, string|mixed[]> $v2 result of method $this->md5FileAsArray()
     * @param bool $show_modif if set to false, the method will only
     *                         list deleted files
     * @param string $path
     *                     deleted files in version $v2. Otherwise, only deleted.
     *
     * @return array{'modified': string[], "deleted": string[]}
     *
     * @internal Made public for tests
     */
    public function compareReleases(array $v1, array $v2, bool $show_modif = true, string $path = '/'): array
    {
        // in that array the list of files present in v1 deleted in v2
        static $deletedFiles = [];
        // in that array the list of files present in v1 modified in v2
        static $modifiedFiles = [];

        foreach ($v1 as $file => $md5) {
            if ($this->filesystemAdapter->isFileSkipped($file, $path . $file, 'upgrade', '')) {
                continue;
            }
            if (is_array($md5)) {
                $subpath = $path . $file;
                if (isset($v2[$file]) && is_array($v2[$file])) {
                    $this->compareReleases($md5, $v2[$file], $show_modif, $path . $file . '/');
                } else { // also remove old dir
                    $deletedFiles[] = $subpath;
                }
            } else {
                if (in_array($file, array_keys($v2))) {
                    if ($show_modif && ($v1[$file] != $v2[$file])) {
                        $modifiedFiles[] = $path . $file;
                    }
                } else {
                    $deletedFiles[] = $path . $file;
                }
            }
        }

        return ['deleted' => $deletedFiles, 'modified' => $modifiedFiles];
    }

    /**
     * Compare the md5sum of the current files with the md5sum of the original.
     *
     * @param string[] $current_path
     */
    protected function browseXmlAndCompare(SimpleXMLElement $node, array &$current_path = [], int $level = 1): void
    {
        foreach ($node as $child) {
            // @phpstan-ignore function.alreadyNarrowedType (Looping on SimpleXMLElement variables brings nullable values before PHP 8)
            if (is_object($child) && $child->getName() === 'dir') {
                $directoryName = (string) $child['name'];
                if ($level === 1 && $directoryName === 'install') {
                    continue;
                }
                $current_path[$level] = $directoryName;
                $this->browseXmlAndCompare($child, $current_path, $level + 1);
            }
            // @phpstan-ignore function.alreadyNarrowedType (Looping on SimpleXMLElement variables brings nullable values before PHP 8)
            elseif (is_object($child) && $child->getName() === 'md5file') {
                // We will store only relative path.
                // absolute path is only used for file_exists and compare
                $relative_path = '';
                for ($i = 1; $i < $level; ++$i) {
                    $relative_path .= $current_path[$i] . '/';
                }
                $relative_path .= $child['name'];

                $fullPath = $this->prodPath . DIRECTORY_SEPARATOR . $relative_path;
                $fullPath = str_replace('ps_root_dir', $this->prodPath, $fullPath);

                // replace default admin dir by current one
                $fullPath = str_replace($this->prodPath . DIRECTORY_SEPARATOR . 'admin', $this->adminPath, $fullPath);

                if (!file_exists($fullPath) && !str_contains($fullPath, 'install' . DIRECTORY_SEPARATOR)) {
                    $this->addFileDifferences($relative_path, true);
                } elseif (!$this->compareChecksum($fullPath, (string) $child) && substr(str_replace(DIRECTORY_SEPARATOR, '-', $relative_path), 0, 7) != 'modules') {
                    $this->addFileDifferences($relative_path);
                }
                // else, file is original (and ok)
            }
        }
    }

    /**
     * @return array<string, string|mixed[]>
     */
    protected function md5FileAsArray(SimpleXMLElement $node, string $dir = '/')
    {
        $array = [];
        foreach ($node as $child) {
            // @phpstan-ignore function.alreadyNarrowedType (Looping on SimpleXMLElement variables brings nullable values before PHP 8)
            if (is_object($child) && $child->getName() == 'dir') {
                $dir = (string) $child['name'];
                /**
                 * $current_path = $dir.(string)$child['name'];.
                 *
                 * @todo : something else than array pop ?
                 */
                $dir_content = $this->md5FileAsArray($child, $dir);
                $array[$dir] = $dir_content;
            }
            // @phpstan-ignore function.alreadyNarrowedType (Looping on SimpleXMLElement variables brings nullable values before PHP 8)
            elseif (is_object($child) && $child->getName() == 'md5file') {
                $array[(string) $child['name']] = (string) $child;
            }
        }

        return $array;
    }

    /** populate $this->$this->file_differences with $path
     * in sub arrays  mail, themes, translation and core items.
     *
     * @param string $path filepath to add, relative to _PS_ROOT_DIR_
     */
    protected function addFileDifferences(string $path, bool $isDeletedFile = false): void
    {
        $key = $isDeletedFile ? self::FILE_MISSING : self::FILE_ALTERED;

        $categories = [
            self::CATEGORY_MAIL => ['mails/'],
            self::CATEGORY_TRANSLATION => [
                '/en.php', '/fr.php', '/es.php', '/it.php', '/de.php',
                'translations/',
            ],
            self::CATEGORY_THEME => ['themes/'],
        ];

        foreach ($categories as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($path, $pattern) !== false) {
                    $this->fileDifferences[$category][$key][] = $path;

                    return;
                }
            }
        }

        $this->fileDifferences[self::CATEGORY_CORE][$key][] = $path;
    }

    protected function compareChecksum(string $filepath, string $md5sum): bool
    {
        return md5_file($filepath) == $md5sum;
    }
}
