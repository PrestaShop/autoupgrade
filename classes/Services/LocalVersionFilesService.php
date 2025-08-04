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

namespace PrestaShop\Module\AutoUpgrade\Services;

use Exception;

class LocalVersionFilesService
{
    const TYPE_ZIP = 'zip';
    const TYPE_XML = 'xml';

    /** @var PrestashopVersionService */
    private $prestashopVersionService;
    /** @var string */
    private $downloadPath;
    /** @var string */
    private $currentVersion;

    public function __construct(
        PrestashopVersionService $prestashopVersionService,
        string $downloadPath,
        string $currentVersion
    ) {
        $this->prestashopVersionService = $prestashopVersionService;
        $this->downloadPath = $downloadPath;
        $this->currentVersion = $currentVersion;
    }

    /**
     * @param string $folderPath
     * @param string $extension
     *
     * @return string[]
     */
    public function getAllFilesFromFolder(string $folderPath, string $extension): array
    {
        if (!is_dir($folderPath)) {
            return [];
        }

        return glob($folderPath . DIRECTORY_SEPARATOR . '*.' . $extension);
    }

    /**
     * @return array<string, array{'zip': string[], 'xml': string[]}>
     *
     * @throws Exception
     */
    public function getLocalVersionsFiles(): array
    {
        $zipFiles = $this->getAllFilesFromFolder($this->downloadPath, self::TYPE_ZIP);

        $zipFiles = array_map(function ($zip) {
            $version = $this->prestashopVersionService->extractPrestashopVersionFromZip($zip);

            return ['filename' => basename($zip), 'version' => $version];
        }, $zipFiles);

        $xmlFiles = $this->getAllFilesFromFolder($this->downloadPath, self::TYPE_XML);

        $xmlFiles = array_map(function ($xml) {
            $version = $this->prestashopVersionService->extractPrestashopVersionFromXml($xml);

            return ['filename' => basename($xml), 'version' => $version];
        }, $xmlFiles);

        $groupedByVersion = [];

        $this->groupFilesByVersion($zipFiles, self::TYPE_ZIP, $groupedByVersion);
        $this->groupFilesByVersion($xmlFiles, self::TYPE_XML, $groupedByVersion);

        return array_filter($groupedByVersion, function ($files, $version) {
            $isRequiredFilesNotPresent = !empty($files[self::TYPE_XML]) && !empty($files[self::TYPE_ZIP]);
            $isInferiorVersion = version_compare($this->currentVersion, $version) === -1;

            return $isRequiredFilesNotPresent && $isInferiorVersion;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param array<int, array{'version': string, 'filename': string}> $files
     * @param self::TYPE_* $type
     * @param array<string, array{'zip': string[], 'xml': string[]}> $groupedByVersion
     *
     * @return void
     */
    private function groupFilesByVersion(array $files, string $type, array &$groupedByVersion): void
    {
        foreach ($files as $file) {
            $version = $file['version'];
            $filename = $file['filename'];

            if (!isset($groupedByVersion[$version])) {
                $groupedByVersion[$version] = [
                    'zip' => [],
                    'xml' => [],
                ];
            }

            $groupedByVersion[$version][$type][] = $filename;
        }
    }

    /**
     * @return array{'zip': string[], 'xml': string[]}
     *
     * @throws Exception
     */
    public function getFlatZipAndXmlLists(): array
    {
        $localVersionsFiles = $this->getLocalVersionsFiles();

        $zipList = [];
        $xmlList = [];

        foreach ($localVersionsFiles as $files) {
            if (!empty($files[self::TYPE_ZIP])) {
                $zipList = array_merge($zipList, $files[self::TYPE_ZIP]);
            }
            if (!empty($files[self::TYPE_XML])) {
                $xmlList = array_merge($xmlList, $files[self::TYPE_XML]);
            }
        }

        return [
            self::TYPE_ZIP => $zipList,
            self::TYPE_XML => $xmlList,
        ];
    }
}
