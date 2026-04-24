<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
    const MAX_SQL_SIZE_TO_WRITE_PER_BATCH = 'max_sql_size_to_write_per_batch';

    const BACKUP_CONST_KEYS = [
        self::KEEP_IMAGES,
        self::MAX_FILES_PER_BATCH,
        self::MAX_FILE_SIZE_ALLOWED,
        self::MAX_SQL_SIZE_TO_WRITE_PER_BATCH,
    ];

    const DEFAULT_VALUES = [
        self::KEEP_IMAGES => true,
        self::MAX_FILES_PER_BATCH => 400,
        self::MAX_FILE_SIZE_ALLOWED => 15728640,
        self::MAX_SQL_SIZE_TO_WRITE_PER_BATCH => 4194304,
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
    public function getMaxSqlSizeToWritePerBatch(): int
    {
        return $this->computeIntConfiguration(self::MAX_SQL_SIZE_TO_WRITE_PER_BATCH);
    }
}
