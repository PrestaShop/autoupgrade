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

namespace PrestaShop\Module\AutoUpgrade\Backup;

use DateTime;
use IntlDateFormatter;
use PrestaShop\Module\AutoUpgrade\Exceptions\BackupException;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class BackupFinder
{
    const BACKUP_ZIP_NAME_PREFIX = 'auto-backupfiles_';
    const BACKUP_DB_FOLDER_NAME_PREFIX = 'auto-backupdb_';
    /**
     * @var string[]|null
     */
    private $availableBackups;

    /**
     * @var string
     */
    private $backupPath;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator, string $backupPath)
    {
        $this->translator = $translator;
        $this->backupPath = $backupPath;
    }

    /**
     * @return string[]
     */
    public function getAvailableBackups(): array
    {
        if (null === $this->availableBackups) {
            $this->availableBackups = $this->buildBackupList();
        }

        return $this->availableBackups;
    }

    /**
     * @return array<array{timestamp: int, datetime: string, version:string, filename: string}>
     */
    public function getSortedAndFormatedAvailableBackups(): array
    {
        $backupsAvailable = $this->getAvailableBackups();
        $backupsFormated = array_map([$this, 'parseBackupMetadata'], $backupsAvailable);

        $this->sortBackupsByNewest($backupsFormated);

        return $backupsFormated;
    }

    public function getBackupPath(): string
    {
        return $this->backupPath;
    }

    public function resetBackupList(): void
    {
        $this->availableBackups = null;
    }

    /**
     * @return string[]
     */
    private function buildBackupList(): array
    {
        return array_intersect(
            $this->getBackupDbAvailable($this->backupPath),
            $this->getBackupFilesAvailable($this->backupPath)
        );
    }

    /**
     * @return string[]
     */
    private function getBackupDbAvailable(string $backupPath): array
    {
        $array = [];

        $files = scandir($backupPath);

        foreach ($files as $file) {
            if ($file[0] == 'V' && is_dir($backupPath . DIRECTORY_SEPARATOR . $file)) {
                $array[] = $file;
            }
        }

        return $array;
    }

    /**
     * @return string[]
     */
    private function getBackupFilesAvailable(string $backupPath): array
    {
        $array = [];
        $files = scandir($backupPath);

        foreach ($files as $file) {
            if ($file[0] != '.' && substr($file, 0, 16) == 'auto-backupfiles') {
                $array[] = preg_replace('#^' . self::BACKUP_ZIP_NAME_PREFIX . '(.*-[0-9a-f]{1,8})\..*$#', '$1', $file);
            }
        }

        return $array;
    }

    /**
     * @param string $backupName
     *
     * @return array{timestamp: int, datetime: string, version:string, filename: string}
     *
     * @throws BackupException
     */
    public function parseBackupMetadata(string $backupName): array
    {
        $pattern = '/V(\d+(\.\d+){1,3})_([0-9]{8})-([0-9]{6})/';
        if (preg_match($pattern, $backupName, $matches)) {
            $version = $matches[1];
            $datePart = $matches[3];
            $timePart = $matches[4];

            $dateTime = DateTime::createFromFormat('Ymd His', $datePart . ' ' . $timePart);
            $timestamp = $dateTime->getTimestamp();

            return
                [
                    'timestamp' => $timestamp,
                    'datetime' => $this->getFormattedDatetime($timestamp),
                    'version' => $version,
                    'filename' => $backupName,
                ];
        }

        throw new BackupException($this->translator->trans('An error occurred while formatting the backup name.'));
    }

    /**
     * @param array<mixed, array{timestamp: int, datetime: string, version:string, filename: string}> $backups
     */
    public function sortBackupsByNewest(array &$backups): void
    {
        // Most recent first
        usort($backups, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });
    }

    private function getFormattedDatetime(int $timestamp): string
    {
        // Check if Intl extension is loaded
        if (extension_loaded('intl')) {
            $locale = $this->translator->getLocale();

            $formatter = new IntlDateFormatter(
                $locale,
                IntlDateFormatter::SHORT,
                IntlDateFormatter::SHORT
            );

            return $formatter->format($timestamp);
        }

        // Fallback if Intl extension is not loaded
        setlocale(LC_TIME, '');

        return strftime('%x %X', $timestamp);
    }
}
