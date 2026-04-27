<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters;

/**
 * Contains the restore process configuration.
 */
class RestoreConfiguration extends AbstractConfiguration
{
    const BACKUP_NAME = 'backup_name';
    const MAX_SECONDS_PER_BATCH = 'max_seconds_per_batch';

    const RESTORE_CONST_KEYS = [
        self::BACKUP_NAME,
        self::MAX_SECONDS_PER_BATCH,
    ];

    const DEFAULT_VALUES = [
        self::MAX_SECONDS_PER_BATCH => 6,
    ];

    public function getBackupName(): ?string
    {
        return $this->get(self::BACKUP_NAME);
    }

    /**
     * @return int Number of seconds allowed before having to make another request
     */
    public function getMaxSecondsPerBatch(): int
    {
        return $this->computeIntConfiguration(self::MAX_SECONDS_PER_BATCH);
    }
}
