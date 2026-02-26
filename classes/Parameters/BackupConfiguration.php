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

namespace PrestaShop\Module\AutoUpgrade\Parameters;

/**
 * Contains the backup process configuration.
 */
class BackupConfiguration extends AbstractConfiguration
{
    const KEEP_IMAGES = 'keep_images';
    const MAX_FILES_PER_BATCH = 'max_files_per_batch';
    const MAX_FILE_SIZE_ALLOWED = 'max_file_size_allowed';
    const MAX_SQL_SIZE_TO_WRITE_PER_CALL = 'max_sql_size_to_write_per_call';

    const BACKUP_CONST_KEYS = [
        self::KEEP_IMAGES,
        self::MAX_FILES_PER_BATCH,
        self::MAX_FILE_SIZE_ALLOWED,
        self::MAX_SQL_SIZE_TO_WRITE_PER_CALL,
    ];

    const DEFAULT_VALUES = [
        self::KEEP_IMAGES => true,
        self::MAX_FILES_PER_BATCH => 400,
        self::MAX_FILE_SIZE_ALLOWED => 15728640,
        self::MAX_SQL_SIZE_TO_WRITE_PER_CALL => 4194304,
    ];

    /**
     * @return bool True if the autoupgrade module backup should include the images
     */
    public function shouldBackupImages(): bool
    {
        return $this->computeBooleanConfiguration(self::KEEP_IMAGES);
    }

    /**
     * @return int Number of files to handle in a single call to avoid timeouts
     */
    public function getMaxFilesPerBatch(): int
    {
        return $this->computeIntConfiguration(self::MAX_FILES_PER_BATCH);
    }

    /**
     * @return int Max file size allowed in backup
     */
    public function getMaxFileSizeAllowed(): int
    {
        return $this->computeIntConfiguration(self::MAX_FILE_SIZE_ALLOWED);
    }

    /**
     * @return int Kind of reference for SQL file creation, giving a file size before another request is needed
     */
    public function getMaxSqlSizeToWritePerCall(): int
    {
        return $this->computeIntConfiguration(self::MAX_SQL_SIZE_TO_WRITE_PER_CALL);
    }
}
